(function ($) {
    "use strict";

    $.fn.select2.amd.define('optgroup-data', ['select2/data/select', 'select2/utils'], function (SelectAdapter, Utils) {
        function OptgroupData($element, options) {
            OptgroupData.__super__.constructor.apply(this, arguments);
            this._checkOptgroups();
        }

        Utils.Extend(OptgroupData, SelectAdapter);

        OptgroupData.prototype.current = function (callback) {
            let data = [];
            let self = this;
            this._checkOptgroups();

            this.$element.find(':not(.selected-custom) :selected, .selected-custom').each(function () {
                let $option = $(this);
                let option = self.item($option);

                if (!option.hasOwnProperty('id')) {
                    option.id = 'optgroup';
                }

                data.push(option);
            });

            callback(data);
        };

        OptgroupData.prototype.bind = function (container, $container) {
            OptgroupData.__super__.bind.apply(this, arguments);
            let self = this;

            container.on('optgroup:select', function (params) {
                self.optgroupSelect(params.data);
            });

            container.on('optgroup:unselect', function (params) {
                self.optgroupUnselect(params.data);
            });
        };

        OptgroupData.prototype.select = function (data) {
            if ($(data.element).is('optgroup')) {
                this.optgroupSelect(data);
                return;
            }

            // Change selected property on underlying option element
            data.selected = true;
            data.element.selected = true;

            this.$element.trigger('change');
            this.clearSearch();

            // Manually trigger dropdrop positioning handler
            $(window).trigger('scroll.select2');
        };

        OptgroupData.prototype.unselect = function (data) {
            if ($(data.element).is('optgroup')) {
                this.optgroupUnselect(data);
                return;
            }

            // Change selected property on underlying option element
            data.selected = false;
            data.element.selected = false;

            this.$element.trigger('change');

            // Manually trigger dropdrop positioning handler
            $(window).trigger('scroll.select2');
        };

        OptgroupData.prototype.optgroupSelect = function (data) {
            data.selected = true;
            let vals = this.$element.val() || [];

            let newVals = $.map(data.element.children, function (child) {
                return '' + child.value;
            });

            newVals.forEach(function (val) {
                if ($.inArray(val, vals) === -1) {
                    vals.push(val);
                }
            });

            this.$element.val(vals);
            this.$element.trigger('change');
            this.clearSearch();

            // Manually trigger dropdrop positioning handler
            $(window).trigger('scroll.select2');
        };

        OptgroupData.prototype.optgroupUnselect = function (data) {
            data.selected = false;
            let vals = this.$element.val() || [];
            let removeVals = $.map(data.element.children, function (child) {
                return '' + child.value;
            });
            let newVals = [];

            vals.forEach(function (val) {
                if ($.inArray(val, removeVals) === -1) {
                    newVals.push(val);
                }
            });
            this.$element.val(newVals);
            this.$element.trigger('change');

            // Manually trigger dropdrop positioning handler
            $(window).trigger('scroll.select2');
        };

        // Check if all children of optgroup are selected. If so, select optgroup
        OptgroupData.prototype._checkOptgroups = function () {
            this.$element.find('optgroup').each(function () {
                let children = this.children;

                let allSelected = !!children.length;

                for (let i = 0; i < children.length; i++) {
                    allSelected = children[i].selected;
                    if (!allSelected) {
                        break;
                    }
                }

                $(this).toggleClass('selected-custom', allSelected);
            });

        };

        OptgroupData.prototype.clearSearch = function () {
            if (!this.container) {
                return;
            }

            if (this.container.selection.$search.val()) {
                this.container.selection.$search.val('');
                this.container.selection.handleSearch();
            }
        }

        return OptgroupData;
    });

    $.fn.select2.amd.define('optgroup-results', ['select2/results', 'select2/utils', 'select2/keys'], function OptgroupResults(ResultsAdapter, Utils, KEYS) {
        function OptgroupResults() {
            OptgroupResults.__super__.constructor.apply(this, arguments);
        };

        Utils.Extend(OptgroupResults, ResultsAdapter);

        OptgroupResults.prototype.option = function (data) {
            let option = OptgroupResults.__super__.option.call(this, data);

            if (data.children) {
                let $label = $(option).find('.select2-results__group');
                $label.attr({
                    'role': 'treeitem',
                    'aria-selected': 'false'
                });

                $label.data('data', data);
            }

            return option;
        };

        OptgroupResults.prototype.bind = function (container, $container) {
            OptgroupResults.__super__.bind.call(this, container, $container);
            let self = this;

            this.$results.on('mouseup', '.select2-results__group', function (evt) {
                let $this = $(this);
                let data = $this.data('data');
                let trigger = ($this.attr('aria-selected') === 'true') ? 'optgroup:unselect' : 'optgroup:select';

                self.trigger(trigger, {
                    originalEvent: evt,
                    data: data
                });

                return false;
            });

            this.$results.on('mouseenter', '.select2-results__group[aria-selected]', function () {
                let data = $(this).data('data');

                self.getHighlightedResults()
                    .removeClass('select2-results__option--highlighted');

                self.trigger('results:focus', {
                    data: data,
                    element: $(this)
                });
            });

            container.on('optgroup:select', function () {
                if (!container.isOpen()) {
                    return;
                }

                if (self.options.options.closeOnSelect) {
                    self.trigger('close');
                }

                self.setClasses();
            });

            container.on('optgroup:unselect', function () {
                if (!container.isOpen()) {
                    return;
                }

                self.setClasses();
            });
        };

        OptgroupResults.prototype.setClasses = function () {
            let self = this;

            this.data.current(function (selected) {
                let selectedIds = [];
                let optgroupLabels = [];

                $.each(selected, function (i, obj) {
                    if (obj.children) {
                        optgroupLabels.push(obj.text);
                        $.each(obj.children, function (j, child) {
                            selectedIds.push(child._resultId);
                        });
                    } else {
                        selectedIds.push(obj._resultId);
                    }
                });

                let $options = self.$results.find('.select2-results__option[aria-selected]');

                $options.each(function () {
                    let $option = $(this);
                    let id = $option.attr('id');

                    if ($.inArray(id, selectedIds) > -1) {
                        $option.attr('aria-selected', 'true');
                    } else {
                        $option.attr('aria-selected', 'false');
                    }
                });


                let $groups = self.$results.find('.select2-results__group[aria-selected]');

                $groups.each(function () {
                    let $optgroup = $(this);
                    let item = $.data(this, 'data');
                    let text = item.text;
                    let $element = $(item.element);

                    if ($element.hasClass('selected-custom') || $.inArray(text, optgroupLabels) > -1) {
                        $optgroup.attr('aria-selected', 'true');
                    } else {
                        $optgroup.attr('aria-selected', 'false');
                    }
                });

                if (!self.getHighlightedResults().length) {
                    $('.select2-results__option[aria-selected]').first().trigger('mouseenter');
                }
            });
        };

        return OptgroupResults;
    });
})(jQuery);
