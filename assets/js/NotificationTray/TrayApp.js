'use strict';

import React, { Component } from 'react'
import MessageWall from './MessageWall'
import Notifications from './Notifications'
import PropTypes from 'prop-types'
import {openPage} from "../component/openPage"
import {fetchJson} from "../component/fetchJson"

export default class TrayApp extends Component {
    constructor (props) {
        super(props)
        this.otherProps = {...props}
        this.state = {
            notificationCount: 0,
            messengerCount: 0,
            notificationTitle: 'Notifications',
            messengerTitle: 'Message Wall',
        }
        this.timeout = this.isStaff === true ? 10000 : 120000
        this.showNotifications = this.showNotifications.bind(this)
        this.showMessenger = this.showMessenger.bind(this)
        this.handleLogout = this.handleLogout.bind(this)
        this.displayTray = true
        this.delay = ( function() {
            var timer = 0;
            return function(callback, ms) {
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        })()
    }

    componentDidMount () {
        if (this.displayTray){
            this.loadNotification(250 + 2000 * Math.random())
            this.loadMessenger(250 + 2000 * Math.random())
        }
    }

    componentWillUnmount() {
        clearTimeout(this.notificationTime);
        clearTimeout(this.messengerTime);
    }

    loadNotification(timeout){
        this.notificationTime = setTimeout(() => {
            fetchJson('api/notification/refresh/', {method: 'GET'}, false)
                .then(data => {
                    if (data.count !== this.state.notiificationCount) {
                        this.setState({
                            notificationCount: data.count,
                            notificationTitle: data.title,
                        })
                    }
                    if (data.redirect)
                        openPage('/')
                })
            this.loadNotification(this.timeout)
        }, timeout)
    }

    loadMessenger(timeout){
        this.messengerTime = setTimeout(() => {
            fetchJson('api/messenger/refresh/', {method: 'GET'}, false)
                .then(data => {
                    if (data.count !== this.state.messengerCount) {
                        this.setState({
                            messengerCount: data.count,
                            messengerTitle: data.title,
                        })
                    }
                    if (data.redirect)
                        openPage('/')
                })
            this.loadMessenger(this.timeout)
        }, timeout)
    }

    showNotifications() {
        if (this.state.notificationCount > 0)
            openPage('/notifications/manage/', {method: 'GET'}, false);
    }

    showMessenger() {
        if (this.state.messengerCount > 0)
            openPage('/messenger/today/show/', {method: 'GET'}, false);
    }

    handleLogout() {
        openPage('/logout/', {method: 'GET'}, false);
    }

    render () {
        return (
            <div className={'flex flex-row-reverse mb-1'}>
                <MessageWall messengerCount={this.state.messengerCount} showMessenger={this.showMessenger} title={this.state.messengerTitle} />
                <Notifications notificationCount={this.state.notificationCount} showNotifications={this.showNotifications} title={this.state.notificationTitle} />
            </div>
        )
    }
}

TrayApp.propTypes = {
    displayTray: PropTypes.bool,
    locale: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.bool,
    ]),
}

TrayApp.defaultProps = {
    displayTray: false,
    locale: false,
}
