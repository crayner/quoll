'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import RenderPeople from "./RenderPeople"
import {fetchJson} from "../component/fetchJson"

export default class PhotoLoaderApp extends Component {
    constructor (props) {
        super(props)
        this.people = props.people
        this.messages = props.messages
        this.absolute_url = props.absolute_url
        this.timeout = null

        this.selectPerson = this.selectPerson.bind(this)
        this.addMessage = this.addMessage.bind(this)
        this.validateImage = this.validateImage.bind(this)
        this.replacePerson = this.replacePerson.bind(this)
        this.removePhoto = this.removePhoto.bind(this)

        this.state = {
            chosen: {},
            messages: [],
        }
        console.log(this)
    }

    addMessage(message, status)
    {
        message = {
            status: status,
            message: message
        }
        clearTimeout(this.timeout)
        let messages = this.state.messages
        messages.push(message)
        this.setState({
            messages: messages,
        })
        this.timeout = setTimeout(() => {
            this.setState({
                messages: []
            })
        }, 5000)
    }

    selectPerson({target} = e) {
        let person = {}
        let found = false
        const value = parseInt(target.value, 10) > 0 ? parseInt(target.value, 10) : 0
        Object.keys(this.people).map(group => {
            if (found) return
            const groupData = this.people[group]
            Object.keys(groupData).map(name => {
                if (found) return
                const x = groupData[name]
                if(x.id === value) {
                    found = true
                    person = {...x}
                }
            })
        })

        this.setState({
            chosen: person,
            messages: [],
        })
    }

    validateImage({meta} = filexhrmeta) {
        if (meta.height > 960 || meta.height < 240 || meta.width > 720)
            return true
        const ratio = meta.width / meta.height
        if (ratio < 0.7 || ratio > 0.84)
            return true
        return false
    }

    replacePerson(person){
        let found = false
        const value =person.id
        Object.keys(this.people).map(group => {
            if (found) return
            const groupData = this.people[group]
            Object.keys(groupData).map(name => {
                if (found) return
                const x = groupData[name]
                if(x.id === value) {
                    found = true
                    this.people[group][name] = {...person}
                }
            })
        })

        this.setState({
            chosen: person,
        })
    }

    removePhoto(person){
        let url = this.absolute_url + '/personal/photo/{person}/remove/'
        url = url.replace('{person}', person.id)
        fetchJson(url,
            {},
            false
            ).then(data => {
                if (data.status === 'success') {
                    this.replacePerson(data.person)
                } else if (data.status === 'error') {
                    this.addMessage(data.message, 'error')
                }
            }).catch(error => {
                this.addMessage(error, 'error')
            })

    }

    render () {
        let x = 0
        const messages = this.state.messages.map(message => {
            x = x + 1
            return (<div className={message.status} key={x}>{message.message}</div>)
        })

        return (
            <div>
                {messages}
                <RenderPeople people={this.people} chosen={this.state.chosen} selectPerson={this.selectPerson} addMessage={this.addMessage} validateImage={this.validateImage} replacePerson={this.replacePerson} removePhoto={this.removePhoto} messages={this.messages} absolute_url={this.absolute_url} />
                <h3>{this.messages['Import Images']}</h3>
                <div className="info clear-both">
                    <h4 className="info">{this.messages['Notes']}</h4>
                    <p>{this.messages['drag_drop_page']}</p>
                    <ol>
                        <li>{this.messages['File Name - The system modifies the filename when linked to the correct person.']}</li>
                        <li>{this.messages['File Type * - Images must be formatted as JPG or PNG.']}</li>
                        <li>{this.messages['Image Size * - Displayed at 240px by 320px.']}</li>
                        <li>{this.messages['Size Range * - Accepts images up to 720px by 960px.']}</li>
                        <li>{this.messages['Aspect Ratio Range * - Accepts aspect ratio between 0.7:1 and 0.84:1.']}</li>
                    </ol>
                </div>
            </div>
        )
    }
}

PhotoLoaderApp.propTypes = {
    people: PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired,
    absolute_url: PropTypes.string.isRequired,
}
