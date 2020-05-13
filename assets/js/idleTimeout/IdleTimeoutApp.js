'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import IdleTimer from 'react-idle-timer'
import {openPage} from "../component/openPage"

export default class IdleTimeoutApp extends Component {
    constructor (props) {
        super(props)
        this.idleTimer = null
        this.state = {
            timeout: 1000 * props.duration,
            remaining: null,
            lastActive: null,
            elapsed: null,
            display: false,
        }
        // Bind event handlers and methods
        this.onActive = this._onActive.bind(this)
        this.onIdle = this._onIdle.bind(this)
        this.reset = this._reset.bind(this)
        this.changeTimeout = this._changeTimeout.bind(this)
        this.route = props.route
    }

    componentDidMount () {
        if (this.idleTimer !== null) {
            this.setState({
                remaining: this.idleTimer.getRemainingTime(),
                lastActive: this.idleTimer.getLastActiveTime(),
                elapsed: this.idleTimer.getElapsedTime(),
            })

            setInterval(() => {
                this.setState({
                    remaining: this.idleTimer.getRemainingTime(),
                    lastActive: this.idleTimer.getLastActiveTime(),
                    elapsed: this.idleTimer.getElapsedTime(),
                    display: this.state.timeout - this.idleTimer.getElapsedTime() > 30000 ? false : true,
                })
                if (this.wasLastActive !== this.idleTimer.getLastActiveTime())
                    this.refreshPage()
                this.wasLastActive = this.idleTimer.getLastActiveTime()
                if (this.state.elapsed > this.state.timeout)
                    this.logout()
            }, 1000)
        }
    }

    render () {
        return (
            <section>
                <IdleTimer
                    ref={ref => { this.idleTimer = ref }}
                    onActive={this.onActive}
                    onIdle={this.onIdle}
                    timeout={this.state.timeout}
                    throttle={50}
                    startOnLoad
                />
                { this.state.display ?
                    <div className={'absolute w-full top-0 left-0 min-h-full bg-gray-900'} style={{zIndex: 99999 }}>
                        <div className={'absolute w-full top-0 left-0 min-h-screen'}>
                            <div className={'bg-orange-700 absolute border-color-white border-4 rounded-lg h-40 w-64'} style={{transform: 'translate(-50%,-35%)', top: '35%', left: '50%'}}>
                                <div className={'w-full p-2'}>
                                    <img className={'float-right'} src={'/build/static/kookaburra.png'} height={75} />
                                    <h3 className={'absolute top-0 left-0 pt-10 px-2 m-0 ml-1 border-color-white border-b-2'}>Kookaburra</h3>
                                    <span className={'float-left'}>{ this.props.trans_sessionExpire }</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    :  '' }
            </section>
        )
    }


    refreshPage(){
        if (this.state.elapsed > this.state.timeout)
            this.logout()
        this.reset()
    }

    _onActive () {
        this.refreshPage()
    }

    _onIdle () {
    }

    _changeTimeout () {
        this.setState({
            timeout: this.refs.timeoutInput.state.value(),
        })
    }

    _reset () {
        this.idleTimer.reset()
        this.setState({
            display: false,
            lastActive: Date.now(),
        })
    }

    logout () {
        window.localStorage.setItem('logged_in', 'false')
        openPage(this.route, {method: 'GET'}, false)
    }
}



IdleTimeoutApp.propTypes = {
    route: PropTypes.string.isRequired,
    duration: PropTypes.number.isRequired,
}