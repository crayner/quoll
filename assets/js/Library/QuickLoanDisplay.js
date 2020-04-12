'use strict'

import React from "react"
import PropTypes from 'prop-types'
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import '../../css/react-tabs.scss';
import Parser from "html-react-parser"
import FormApp from "../Form/FormApp"

export default function QuickLoanDisplay(props) {
    const {
        items,
        functions,
        person,
    } = props

    let display = Object.keys(items).map(key => {
        const item = items[key]
        return (<div key={key} style={{float: 'left', width: '33%' }} className={'text-center'}>
            <span className={'fas text-red-700 fa-eraser fa-fw'} title={functions.translate('Remove Item')} style={{float: 'right', marginLeft: '-25px'}} onClick={(e) => functions.removeItem(e,item.children.id.value)}></span>
            <img src={item.children.imageLocation.value} className={'max200 user'} />
            <p className={'text-center text-gray-600'}>{item.children.name.value}</p>
        </div> )
    })

    if (person.value !== '') {
        display.unshift(<div key={'person'} style={{float: 'left', width: '33%'}} className={'text-center'}>
            <span className={'fas text-red-700 fa-eraser fa-fw'} title={functions.translate('Remove Person')} style={{float: 'right', marginLeft: '-25px'}} onClick={() => functions.removePerson()}></span>
            <img src={person.attr['data-photo']} className={'max200 user'} />
            <p className={'text-center text-gray-600'}>{person.attr['data-name']}</p>
        </div>)
    }

    return (
        <div className={'smallIntBorder fullWidth table'}>
            <h3>{functions.translate('Loan List')}</h3>
            {display}
        </div>
    )
}

QuickLoanDisplay.propTypes = {
    items: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array,
    ]),
    functions: PropTypes.object.isRequired,
}

QuickLoanDisplay.defaultProps = {
    items: []
}

