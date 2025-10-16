const addressData = {
    name: {
        id: '#event_address_name',
        text: 'New address ' + Math.random().toString(36).substring(2, 10),
        tinymce: false
    },
    description: {
        id: '#event_address_description_ifr',
        text: 'Description ' + Math.random().toString(36).substring(2, 10),
        tinymce: true
    },
    street: {
        id: '#event_address_street',
        text: 'Sherborne Court, 180-186 Cromwell Rd, London SW5 0ST, UK',
        tinymce: false
    },
    latitude: {
        id: '#event_address_latitude',
        text: '51.49513119468433',
        tinymce: false
    },
    longitude: {
        id: '#event_address_longitude',
        text: '-0.19253673305342112',
        tinymce: false
    }
}

export {addressData}
