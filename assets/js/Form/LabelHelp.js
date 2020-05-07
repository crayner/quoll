'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {labelAttr} from './buildAttr'
import Parser from "html-react-parser"

export default function LabelHelp(props) {
    const {
        form,
    } = props

    let help = []
    let label_attr = labelAttr(form, 'inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs')
    if (typeof form.help === 'string') {
        help.push(<br key={'newLine'} />)
        help.push(<span key={'message'} className={'text-xs text-gray-600 italic font-normal mt-1 sm:mt-0'} id={form.id + '_help'}>{Parser(form.help)}</span>)
    }
    let required = ''
    if (form.required === true)
        required = ' *'

    return (<label htmlFor={form.id} {...label_attr} >{form.label}{required}{help}</label>)
}

LabelHelp.propTypes = {
    form: PropTypes.object.isRequired,
}