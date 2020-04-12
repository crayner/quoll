'use strict';

import React from 'react'
import { render } from 'react-dom'
import HeaderMenu from "./HeaderMenu/HeaderMenuApp"

const headerMenu = document.getElementById('header-menu')

if (headerMenu === null)
    render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
else
    render(
        <HeaderMenu
            {...window.HEADERMENU_PROPS}
        />,
        headerMenu
    )
