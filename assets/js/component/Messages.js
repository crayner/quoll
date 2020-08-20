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

    componentDidUpdate (prevProps, prevState, snapshot) {
        if (this.props.messages !== prevState.messages) {
            this.setState({
                messages: this.props.messages,
            })
        }
    }

    cancelMessage(id) {
        let messages = this.props.messages
        messages.splice(id,1)
        //  remove allGood and notGood Class
        let w = document.getElementsByClassName("notGood")
        if (w.length > 0) {
            for (let q=0; q<w.length; q++) {
                let item = w.item(q)
                item.classList.remove('notGood')
            }
        }
        w = document.getElementsByClassName("allGood")
        if (w.length > 0) {
            for (let q=0; q<w.length; q++) {
                let item = w.item(q)
                item.classList.remove('allGood')
            }
        }
        this.setState({
            messages: messages
        })
    }

    render() {
        let displayed = []
        let cells = Object.keys(this.state.messages).map(key => {
            let message = this.state.messages[key]
            if (typeof message !== 'undefined') {
                if (typeof message === 'undefined')
                    return ''
                if (typeof message === 'string') {
                    let x = {
                        message: message,
                        class: 'error',
                        close: true,
                        id: key
                    }
                    message = { ...x }
                }
                if (typeof message.close === 'undefined')
                    message.close = true
                message['id'] = key
                if (displayed.includes(message.message)) {
                    return null
                }
                displayed.push(message.message)
                return <Message
                    message={message}
                    translate={this.translate}
                    close={message.close}
                    key={'message_' + message.id}
                    cancelMessage={this.cancelMessage}
                />
            } else {
                return null
            }
        })

        // Remove empty messages
        cells = cells.filter(message => {
            if (message !== null) {
                return message
            }
        })

        if (cells.length === 0)
            return null

        return (<div className={'clear-both react-messages'}>{cells}</div>)
    }
}

Messages.propTypes = {
    messages: PropTypes.array.isRequired,
    translate: PropTypes.func.isRequired
}

