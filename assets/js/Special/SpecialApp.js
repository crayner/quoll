'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import PhotoLoaderApp from "../PhotoLoader/PhotoLoaderApp"
import PermissionApp from "../Permission/PermissionApp"

export default class SpecialApp extends Component {
    constructor (props) {
        super(props)
        this.functions = props.functions
        this.name = props.name

        this.content = props

        this.getContent = this.getContent.bind(this)
    }

    getContent()
    {
        let result = []
        if (this.name === 'photo_importer')
            result.push(<PhotoLoaderApp {...this.content} key={'photo_importer'} />)
        if (this.name === 'permission_manager')
            result.push(<PermissionApp {...this.content} key={'permission_manager'} />)
        return result
    }

    render () {
        return (<section>{this.getContent()}</section>)
    }
}

SpecialApp.propTypes = {
    functions: PropTypes.object.isRequired,
    name: PropTypes.string.isRequired,
}

SpecialApp.defaultProps = {
}