const contactInformationData = {
    skype: {
        id: '#app_user_profile_skype',
        text: 'skypeusername'
    },
    phone: {
        id: '#app_user_profile_phone',
        text: Math.floor(Math.random() * 1000000000)
    }
}

const personalInformationData = {
    degreeBefore: {
        id: '#app_user_profile_degreeBefore',
        text: 'DegreeBefore'
    },
    firstName: {
        id: '#app_user_profile_firstName',
        text: 'New first name ' + Math.random().toString(36).substring(2, 7)
    },
    lastName: {
        id: '#app_user_profile_lastName',
        text: 'New last name ' + Math.random().toString(36).substring(2, 7)
    },
    degreeAfter: {
        id: '#app_user_profile_degreeAfter',
        text: 'DegreeAfter'
    }
}

const messageData = {
    greeting: {
        id: 'app_user_profile_messageGreeting',
        text: 'New greeting ' + Math.random().toString(36).substring(2, 7)
    },
    signature: {
        id: 'app_user_profile_messageSignature',
        text: 'New signature ' + Math.random().toString(36).substring(2, 7)
    }
}

const tabData = {
    tabPersonal: {
        id: '#profile-tab',
    },
    tabMessage: {
        id: '#profile-tab2',
    }
}

export {contactInformationData, personalInformationData, messageData, tabData}
