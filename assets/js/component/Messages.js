'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Message from "./Message"

export default class Messages extends Component {
    constructor(props) {
        super(props)
        this.translate = props.translate
        this.cancelMessage = this.cancelMessage.bind(this)
        this.state = {
            messages: props.messages,
        }
    }

    cancelMessage(id) {
        let messages = this.props.messages
        messages.splice(id,1)
        this.setState({
            messages: messages
        })
    }

    render() {
        let cells = Object.keys(this.state.messages).map(key => {
            let message = this.state.messages[key]
            if (typeof message.close === 'undefined')
                message.close = true
            if (typeof message === 'undefined')
                return ''
            if (typeof message === 'string') {
                let x = {}
                x.message = message
                x.class = 'error'
                x.id = key
                message = {...x}
            }
            message['id'] = key
            return <Message
                message={message}
                translate={this.translate}
                close={message.close}
                key={'message_' + message.id}
                cancelMessage={this.cancelMessage}
            />
        })
        if (cells.length === 0)
            return null

        return (<div className={'clear-both'}>{cells}</div>)
    }
}

Messages.propTypes = {
    messages: PropTypes.array.isRequired,
    translate: PropTypes.func.isRequired
}

