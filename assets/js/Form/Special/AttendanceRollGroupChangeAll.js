'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Widget from '../Widget'

export default function AttendanceRollGroupChangeAll(props) {
    const {
        functions,
        form,
    } = props

    form.children.changeAll.help = ''

    function getSaveAttendance()
    {
        let submit = {...form.children.changeAll}
        submit.name = 'Save Attendance'
        submit.full_name = 'attendance_by_roll_group[saveAttendance]'
        submit.id = 'attendance_by_roll_group_saveAttendance'

        submit.help = ''
        return (<Widget columns={1} functions={functions} form={submit} />)
    }

    function getChangeAllButton()
    {
        let submit = {...form.children.changeAll}
        submit.label = functions.translate('Change All?')
        submit.help = ''
        submit.label_class = ''
        submit.attr = {className: 'button w-32 m-px sm:self-center float-left', title: functions.translate('Change all students to these settings')}
        submit.full_name = 'attendance_by_roll_group[changeAll][submit]'
        submit.id = 'attendance_by_roll_group_changeAll_submit'
        submit.name = 'changeAll'
        let code = {...form.children.code}
        code.attr = {className: 'flex float-left'}
        let reason = {...form.children.reason}
        reason.attr = {className: 'flex float-left'}
        reason.placeholder = ' '
        let comment = {...form.children.comment}
        comment.attr = {className: 'flex float-left'}
        comment.placeholder = ' '
        return (
            <div className={'w-3/4 bg-yellow-200'}>
                <Widget columns={1} functions={functions} form={submit} />
                <Widget columns={1} functions={functions} form={code} />
                <Widget columns={1} functions={functions} form={reason} />
                <Widget columns={1} functions={functions} form={comment} />
            </div>)
    }

    return (
        <tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
            <td colSpan={2} className={'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0'}>
                <div className={'w-full flex flex-wrap items-stretch'}>
                    {getChangeAllButton()}
                    <div className={'w-1/4'}>
                        {getSaveAttendance()}
                    </div>
                </div>
            </td>
        </tr>
    )
}

AttendanceRollGroupChangeAll.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

