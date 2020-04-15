'use strict';

import React from 'react'
import { render } from 'react-dom'
import TrayApp from './NotificationTray/TrayApp'

const tray = document.getElementById('notificationTray')

if (tray === null)
    render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
else
    render(
        <TrayApp />,
        tray
    )
