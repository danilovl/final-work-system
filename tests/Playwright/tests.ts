import {test} from '@playwright/test'
import checkCommonUrl from '@playwright-test/test/common/check-url.spec'

import createConversation from '@playwright-test/test/conversation/create-conversation.spec'
import deleteConversation from '@playwright-test/test/conversation/delete-conversation.spec'
import searchConversation from '@playwright-test/test/conversation/search-conversation.spec'

import createEventAddress from '@playwright-test/test/event/create-event-address.spec'
import createEventCalendar from '@playwright-test/test/event/create-event-calendar.spec'
import deleteEventAddress from '@playwright-test/test/event/delete-event-address.spec'
import deleteEventCalendar from '@playwright-test/test/event/delete-event-calendar.spec'

import login from '@playwright-test/test/security/login.spec'

import changePassword from '@playwright-test/test/user/change-password.spec'
import changeProfileImage from '@playwright-test/test/user/change-profile-image.spec'
import createUser from '@playwright-test/test/user/create-user.spec'
import createUserGroup from '@playwright-test/test/user/create-user-group.spec'
import deleteProfileImage from '@playwright-test/test/user/delete-profile-image.spec'
import editProfile from '@playwright-test/test/user/edit-profile.spec'

import changeTaskStatus from '@playwright-test/test/task/change-status-task.spec'
import createTask from '@playwright-test/test/task/create-task.spec'
import deleteTask from '@playwright-test/test/task/delete-task.spec'
import editTask from '@playwright-test/test/task/edit-task.spec'
import searchTask from '@playwright-test/test/task/search-task.spec'

import createWork from '@playwright-test/test/work/create-work.spec'
import deleteWork from '@playwright-test/test/work/delete-work.spec'
import editWork from '@playwright-test/test/work/edit-work.spec'
import editWorkAuthor from '@playwright-test/test/work/edit-work-author.spec'

test.describe(checkCommonUrl)

test.describe(editProfile)
test.describe(changeProfileImage)
test.describe(deleteProfileImage)
test.describe(createUser)
test.describe(createUserGroup)
test.describe(createWork)
test.describe(createTask)
test.describe(createConversation)
test.describe(searchConversation)
test.describe(createEventAddress)
test.describe(createEventCalendar)

test.describe(editTask)
test.describe(changeTaskStatus)
test.describe(searchTask)
test.describe(editWork)
test.describe(editWorkAuthor)

test.describe(deleteEventCalendar)
test.describe(deleteEventAddress)
test.describe(deleteConversation)
test.describe(deleteTask)
test.describe(deleteWork)

test.describe(changePassword)
test.describe(login)
