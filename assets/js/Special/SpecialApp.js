'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import PhotoLoaderApp from "../PhotoLoader/PhotoLoaderApp"
import PermissionApp from "../Permission/PermissionApp"
import RelationshipApp from '../Family/RelationshipApp'
import RequiredDataUpdates from './RequiredDataUpdates'
import AddressApp from './AddressApp'
import TimetableCalendarMap from './TimetableCalendarMap'

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
        if (this.name === 'photo_importer') {
            result.push(<PhotoLoaderApp {...this.content} functions={this.functions} key={'photo_importer'} />)
        } else if (this.name === 'permission_manager') {
            result.push(<PermissionApp {...this.content} key={'permission_manager'} />)
        } else if (this.name === 'family_relationship_manager') {
            result.push(<RelationshipApp {...this.content} key={'family_relationship_manager'} />)
        } else if (this.name === 'required_data_updates') {
            result.push(<RequiredDataUpdates {...this.content} key={'required_data_updates'} />)
        } else if (this.name === 'address_manager') {
            result.push(<AddressApp {...this.content} key={'address_manager'} />)
        } else if (this.name === 'timetable_calendar_map') {
            result.push(<TimetableCalendarMap {...this.content} functions={this.functions} key={'timetable_calendar_map'} />)
        } else {
            console.log(this)
        }
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