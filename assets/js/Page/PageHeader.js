'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {
    getControlButtons
} from "../Container/ContainerFunctions"

export default function PageHeader(props) {
    const {
        details,
        functions
    } = props

    if (typeof details === 'undefined' || details === null || Object.keys(details).length === 0)
        return ([])

    function getContent(y) {
        if (details.content !== '') {
            y.push(<p {...details.contentAttr} key={'content'}>{details.content}</p>)
            return y
        }
        return y
    }

    let y = []
    console.log(details)
    y.push(<h3 {...details.headerAttr} key={'header'}>{details.header}{getControlButtons(details.returnRoute, details.addElementRoute, functions)}</h3>)

    return (getContent(y))
}

PageHeader.propTypes = {
    functions: PropTypes.object.isRequired,
}