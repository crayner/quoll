'use strict'

import React from 'react'
import { render } from 'react-dom'
import PageApp from "./Page/PageApp"

const target = document.getElementById('wrapOuter')

render(
    <PageApp {...window.PAGE_PROPS} />,
    target
)
