'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Widget from '../Widget'
import AttendanceSummaryStatus from './AttendanceSummaryStatus'

export default function AttendanceSummary(props) {
    const {
        functions,
        form,
    } = props
    console.log(form)

    return (
        <tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
            <td className={'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0'}>
                <label className="inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">{form.label}</label>
            </td>
            <td className={'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0'}>
                <AttendanceSummaryStatus data={form.special_data} />
            </td>
        </tr>
    )
}

AttendanceSummary.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

