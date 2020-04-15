'use strict'

export function fetchJson(url, options, locale) {

    var headers = {}
    if (options && options.headers) {
        headers = options.headers
        delete options.headers
    }
    headers = ({...headers, 'Content-Type': 'application/json; charset=utf-8'})

    if (locale === null)
        locale = 'en_GB'

    if (locale === false || url === '')
        locale = ''
    let host = window.location.protocol + '//' + window.location.hostname + '/'

    if (url[0] === '/') {
        url = url.substring(1);
    }

    if (url.substring(0,4) === 'http')
        host = ''

    return fetch(host + locale + url, ({
        ...options,
        credentials: 'same-origin',
        headers: headers,
    }))
        .then(checkStatus)
        .then(response => {
            // decode JSON, but avoid problems with empty responses
            return response.text()
                .then(text => {
                    text = text.replace('<?php', '')
                    if (text.includes('window.Sfdump') || text.includes('<?php'))
                    {
                        console.log(text)
                        return []
                    }
                    return typeof(text) === 'string' ? JSON.parse(text) : ''
                })
        })
}

function checkStatus(response) {
    if (response.status >= 200 && response.status < 400) {
        return response;
    }

    const error = new Error(response.statusText);
    error.response = response;

    throw error
}

