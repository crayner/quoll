'use strict';

import React from 'react'
import { render } from 'react-dom'
import SideBar from "./SideBar/SideBarApp"

const sideBar = document.getElementById('sideBarWrap')

if (sideBar === null)
    render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
else
    render(
        <SideBar
            {...window.SIDEBAR_PROPS}
        />,
        sideBar
    )
