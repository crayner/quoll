'use strict'

import React from "react"
import PropTypes from 'prop-types'
import SideBarContent from "./SideBarContent"

export default function SideBarControl(props) {
    const {
        content,
        state,
        functions,
    } = props

    return (<div>
        <button {...state.buttonAttr} onClick={() => functions.onSetSidebarOpen('open')}>
            <span className={'fas fa-bars fa-fw fa-2x'}/></button>
        <SideBarContent content={content} functions={functions} {...state} />
    </div>)
}

SideBarControl.propTypes = {
    content: PropTypes.object,
    state: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}
SideBarControl.defaultProps = {
    content: {},
}