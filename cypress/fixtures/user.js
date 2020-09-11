const successUserData = {
    'degreeBefore': {
        'id': '#user_degreeBefore',
        'text': 'Degree before ' + Math.random().toString(36).substr(2, 5)
    },
    'firstName': {
        'id': '#user_firstName',
        'text': 'First name ' + Math.random().toString(36).substr(2, 6)
    },
    'lastName': {
        'id': '#user_lastName',
        'text': 'Last name ' + Math.random().toString(36).substr(2, 7)
    },
    'degreeAfter': {
        'id': '#user_degreeAfter',
        'text': 'Degree after ' + Math.random().toString(36).substr(2, 3)
    },
    'phone': {
        'id': '#user_phone',
        'text': Math.floor(Math.random() * 1000000000)
    },
    'email': {
        'id': '#user_email',
        'text': Math.random().toString(36).substr(2, 8) + '@gmail.com'
    },
    'username': {
        'id': '#user_username',
        'text': Math.random().toString(36).substr(2, 8)
    }
}

export {successUserData}