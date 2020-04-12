'use strict';

import React from "react"
import PropTypes from 'prop-types'

export default function AreYouSureDialog(props) {
    const {
        messages,
        doit,
        cancel,
        confirm,
    } = props

    let classname = 'overlay'
    if (confirm)
        classname = 'overlay target'

    return (
        <div id="areYouSure" className={classname}>
            <div className="popup">
                <h3>{ messages['Are you sure you want to delete this record?'] }</h3>
                <a className="close" onClick={() => cancel()} title={messages['Close']}><span className="far fa-times-circle fa-fw"></span></a>
                <div className="content">
                    <p style={{color: '#cc0000'}}>{messages['This operation cannot be undone, and may lead to loss of vital data in your system. PROCEED WITH CAUTION!']}</p>
                    <button type={'button'} className="button btn-gibbon" style={{color: 'white', float: 'right'}} onClick={() => doit()}>{messages['Yes']}</button>
                </div>
            </div>
        </div>
    )
}

AreYouSureDialog.propTypes = {
    messages: PropTypes.object.isRequired,
    doit: PropTypes.func.isRequired,
    cancel: PropTypes.func.isRequired,
    confirm: PropTypes.bool.isRequired,
}
