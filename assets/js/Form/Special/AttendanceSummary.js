'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import { getAttendanceSummaryStatus } from './AttendanceSummaryStatus'

export default function AttendanceSummary(props) {
    const {
        functions,
        form,
    } = props

    return (
        <tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
            <td className={'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0'}>
                <label className="inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">{form.label}</label>
            </td>
            <td className={'w-full max-w-full sm:max-w-xs flex flex-col justify-between items-center px-2 border-b-0 sm:border-b border-t-0'}>
                {getAttendanceSummaryStatus(form.special_data, functions)}
            </td>
        </tr>
    )
}

AttendanceSummary.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

