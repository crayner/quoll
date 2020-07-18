'use strict'

import React from "react"
import PropTypes from 'prop-types'
import FormApp from '../Form/FormApp'

export default function SectionForm(props) {
    const {
        section,
        forms,
        singlePanel,
    } = props

    let form = forms[section.content]
    if (typeof form === 'undefined') {
        const names = Object.keys(forms).filter(name => {
            return name
        })
        console.error('I looked for "' + section.content + '" form in the forms. Available forms are "' + names.toString() + '".')
        return []
    }
    if (singlePanel) {
        return (<form
            action={form.action}
            id={form.id}
            {...form.attr}
            method={form.method !== undefined ? form.method : 'POST'}>
            <FormApp {...props} form={form} formName={section.content} />
        </form>)
    }

    return (<FormApp {...props} form={form} formName={section.content} />)

}

SectionForm.propTypes = {
    section: PropTypes.object.isRequired,
    singlePanel: PropTypes.bool,
}

SectionForm.defaultProps = {
    singlePanel: false
}
