'use strict'

import React from 'react'
import { render } from 'react-dom'
import IdleTimeoutApp from "./idleTimeout/IdleTimeoutApp"

const idleTimeout = document.getElementById('idleTimeout')

render(
    <IdleTimeoutApp
        {...window.IDLETIMEOUT_PROPS}
    />,
    idleTimeout
)
