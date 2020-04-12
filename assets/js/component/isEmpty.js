'use strict'

export function isEmpty(variable) {
    if (variable === undefined)
        return true
    if (variable === '')
        return true
    if (variable === null)
        return true
    if (variable === [])
        return true
    if (variable === {})
        return true
    return false
}
