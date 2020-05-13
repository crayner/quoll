'use strict'

import React from 'react'
import { render } from 'react-dom'
import IdleTimeoutApp from "./idleTimeout/IdleTimeoutApp"
import { openUrl } from './Container/ContainerFunctions'

const idleTimeout = document.getElementById('idleTimeout')

window.localStorage.setItem('logged_in', 'true')
window.addEventListener('storage', storageChange, 'false')

render(
    <IdleTimeoutApp
        {...window.IDLETIMEOUT_PROPS}
    />,
    idleTimeout
)

function storageChange (event) {

    if(event.key === 'logged_in') {
        openUrl('/home/timeout/', '_self')
    }
}
