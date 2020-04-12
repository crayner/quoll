'use strict';

import React from "react"
import PropTypes from 'prop-types'

export default function Notifications(props) {
    const {
        notificationCount,
        showNotifications,
        notificationTitle,
    } = props

    const y = notificationCount

    const colour = 'white'

    return (
        <div id={'notifications'}>
            <a className={y === 0 ? 'inactive inline-block relative mr-4 fa-layers fa-fw fa-3x' : 'inline-block relative mr-4 fa-layers fa-fw fa-3x'} title={notificationTitle} onClick={() => showNotifications()} >
                {y === 0 ?
                    <span className={'far fa-sticky-note text-gray-500 ignore-mouse-down'}>
                    </span>
                    :
                    <span className={'fas fa-sticky-note text-yellow-500 hover:text-orange-500 ignore-mouse-down'}>
                    <span className={'fa-layers-counter absolute'} style={{
                        color: colour,
                        fontSize: '0.8rem',
                        top: '22px',
                        left: '9px'
                    }}>{y}</span>
                    </span>
                }
            </a>
        </div>
    )
}

Notifications.propTypes = {
    notificationCount: PropTypes.number,
    showNotifications: PropTypes.func.isRequired,
    notificationTitle: PropTypes.string,
}

Notifications.defaultProps = {
    notificationCount: 0,
    notificationTitle: 'Notifications',
}
