'use strict';

import React from "react"
import PropTypes from 'prop-types'

export default function MessageWall(props) {
    const {
        messengerCount,
        showMessenger,
        messengerTitle,
    } = props

    const y = messengerCount

    const colour = 'white'

    return (
        <div id="messageWall" className="relative">
            <a href={'#'} title={messengerTitle} className={y === 0 ? 'inactive inline-block relative mr-4 fa-layers fa-fw fa-3x' : 'inline-block relative mr-4 fa-layers fa-fw fa-3x'} onClick={() => showMessenger()}>
                {y === 0 ?
                    <span className={'far fa-comment-dots text-gray-500'}>
                    </span>
                    :
                    <span className={'fas fa-comment-dots text-yellow-500 hover:text-orange-500 ignore-mouse-down'}>
                         <span className={'fa-layers-counter absolute'} style={{color: colour, fontSize: '0.8rem', top: '18px', left: '6px'}}>{y}</span>
                    </span>}
            </a>
        </div>
    )
}

MessageWall.propTypes = {
    messengerCount: PropTypes.number,
    showMessenger: PropTypes.func.isRequired,
    messengerTitle: PropTypes.string,
}

MessageWall.defaultProps = {
    messengerCount: 0,
    messengerTitle: 'Message Wall',
}
