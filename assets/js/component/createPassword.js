'use strict';

export function createPassword(policy) {

    let source = 'abcdefghijklmnopqrstuvwxyz'
    source += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    source += '0123456789';
    source += '#?!@$%^+=&*-';

    let pattern = "^(.*(?=.*[a-z])"
    if (policy.alpha) {
        pattern += "(?=.*[A-Z])"
    }
    if (policy.numeric) {
        pattern += "(?=.*[0-9])"
    }
    if (policy.punctuation) {
        pattern += "(?=.*?[#?!@$%^+=&*-])";
    }

    pattern +=  ".*){" + policy.minLength + ",}$";
    pattern = new RegExp(pattern)
    let minLength = policy.minLength < 12 ? 12 : policy.minLength
    let password = ''
    for (let i = 0; i < minLength; i++) {
        password += source.charAt(Math.floor(Math.random() * source.length))
    }

    while (pattern.test(password) === false) {
        password = ''
        for (let i = 0; i < minLength; i++) {
            password += source.charAt(Math.floor(Math.random() * source.length))
        }
    }
    return password
}
