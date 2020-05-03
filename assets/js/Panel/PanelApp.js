'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Parser from "html-react-parser"
import Panels from "./Panels"
import FormApp from "../Form/FormApp"

export default function PanelApp(props) {
    const {
        panels,
        forms,
        selectedPanel,
        functions,
        singleForm,
        content
    } = props

    let tabIndex = 0
    if (typeof panels[selectedPanel] !== 'undefined')
        tabIndex = panels[selectedPanel].index

    if (Object.keys(panels).length === 1) {
        const name = Object.keys(panels)[0]
        const panel = panels[name]
        if (panel.content !== null) {
            return (
                Parser(panel.content)
            )
        }
        return <FormApp {...props} form={forms[name]} functions={functions} formName={name} singleForm={singleForm} />
    }
    return (
        <Panels {...props} panels={panels} selectedIndex={tabIndex} functions={functions} singleForm={singleForm} externalContent={content} />
    )
}

PanelApp.propTypes = {
    panels: PropTypes.object.isRequired,
    forms: PropTypes.object.isRequired,
    content: PropTypes.object.isRequired,
    selectedPanel: PropTypes.string,
    functions: PropTypes.object.isRequired,
    singleForm: PropTypes.bool.isRequired,
}
