'use strict';

export function openPage(url, options, locale) {

    let target = '_self'
    if (options && typeof(options.target) === 'string') {
        target = options.target
    }

    let specs = ''
    if (options && typeof(options.specs) === 'string') {
        specs = options.specs
    }

    if (typeof locale === 'boolean' && locale === false)
        locale = ''

    if (locale === null || typeof(locale) === 'undefined')
        locale = 'en_GB'

    if (locale !== '')
        locale = '/' + locale

    window.open(window.location.protocol + '//' + window.location.hostname + locale + url, target, specs)
}


