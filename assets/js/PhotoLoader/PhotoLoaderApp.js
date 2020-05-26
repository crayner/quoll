'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import RenderPeople from "./RenderPeople"
import {fetchJson} from "../component/fetchJson"

export default class PhotoLoaderApp extends Component {
    constructor (props) {
        super(props)
        this.messages = props.messages
        this.absolute_url = props.absolute_url
        this.functions = props.functions
        this.timeout = null

        this.addMessage = this.addMessage.bind(this)
        this.validateImage = this.validateImage.bind(this)
        this.replacePerson = this.replacePerson.bind(this)
        this.removePhoto = this.removePhoto.bind(this)

        this.functions.resetSuggestMatches = this.resetSuggestMatches.bind(this)
        this.functions.autoSuggestMatches = this.autoSuggestMatches.bind(this)
        this.functions.getSuggestedValue = this.getSuggestedValue.bind(this)
        this.functions.onChange = this.onChange.bind(this)

        this.state = {
            chosen: {},
            messages: [],
            suggestions: [],
            autoSuggestValue: '',
            people: props.people,
        }
    }

    autoSuggestMatches(value) {
        value = value.value.trim().toLowerCase()
        if (value === '') {
            return this.resetSuggestMatches()
        }
        const suggestions = this.state.people.filter(choice => {
            const label = choice.label.toLowerCase()
            if (label.includes(value)) {
                return choice
            }
        })
        this.setState({
            suggestions: suggestions,
            autoSuggestValue: value,
        })
    }

    onChange(event) {
        if (event.target.value !== undefined)
        {
            this.setState({
                autoSuggestValue: event.target.value,
            })
        }
    }

    resetSuggestMatches() {
        this.setState({
            suggestions: [],
            autoSuggestValue: ''
        })
    }

    getSuggestedValue(suggestion) {
        this.setState({
            chosen: suggestion,
            autoSuggestValue: suggestion.label,
            suggestions: [],
            messages: [],
        })
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

    validateImage({meta} = filexhrmeta) {
        if (meta.height > 960 || meta.height < 240 || meta.width > 720)
            return true
        const ratio = meta.width / meta.height
        if (ratio < 0.7 || ratio > 0.84)
            return true
        return false
    }

    replacePerson(person) {
        let people = this.state.people
        let found = false
        people.map((item,key) => {
            if (item.value === person.value) {
                people[key].photo = person.photo
                person = people[key]
                found = true
            }
        })
        if (found) {
            this.setState({
                chosen: person,
                people: people,
            })
        }
    }

    removePhoto(person){
        let url = this.absolute_url + '/personal/photo/{person}/remove/'
        url = url.replace('{person}', person.value)
        fetchJson(url,
            {},
            false
            ).then(data => {
                if (data.status === 'success') {
                    this.addMessage(data.message,'success')
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
                <RenderPeople functions={this.functions} addMessage={this.addMessage} validateImage={this.validateImage} replacePerson={this.replacePerson} removePhoto={this.removePhoto} absolute_url={this.absolute_url} {...this.state} messages={this.messages} />
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
    people: PropTypes.array.isRequired,
    messages: PropTypes.object.isRequired,
    absolute_url: PropTypes.string.isRequired,
    functions: PropTypes.object.isRequired,
}
