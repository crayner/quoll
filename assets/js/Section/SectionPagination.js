'use strict'

import React from "react"
import PropTypes from 'prop-types'
import FormApp from '../Form/FormApp'

export default function SectionPagination(props) {
    const {
        section,
        singlePanel,
    } = props

    let form = forms[section.content]
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

SectionPagination.propTypes = {
    section: PropTypes.object.isRequired,
    singlePanel: PropTypes.bool,
}

SectionPagination.defaultProps = {
    singlePanel: false
}
