'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Panels from "./Panels"


export default function PanelApp(props) {
    const {
        panels,
        selectedPanel,
        functions,
        singleForm,
        content
    } = props

    let tabIndex = 0
    if (typeof panels[selectedPanel] !== 'undefined') {
        tabIndex = panels[selectedPanel].index
    }

    return (
        <Panels {...props} panels={panels} selectedIndex={tabIndex} functions={functions} singleForm={singleForm} externalContent={content} />
    )
}

PanelApp.propTypes = {
    panels: PropTypes.object.isRequired,
    content: PropTypes.object.isRequired,
    selectedPanel: PropTypes.string,
    functions: PropTypes.object.isRequired,
    singleForm: PropTypes.bool.isRequired,
}
