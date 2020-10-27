'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Widget from '../Widget'

export default function AttendanceStudentCollection(props) {
    const {
        functions,
        form,
    } = props


    let loop = 0

    let present = 0
    let absent = 0

    function getAttendance()
    {
        let inOrOut = form.children[0].children.['inOrOut'].value

        let z = 0
        let xxx = []
        Object.keys(form.children).map(key => {
            let child = form.children[key]
            let code = child.children.code.value

            let classColour = 'text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between w-1/2 sm:w-1/4 bg-gray-200'
            if (typeof inOrOut[code] === 'object') {
                if (inOrOut[code]['direction'] === 'Out') {
                    classColour = 'text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between w-1/2 sm:w-1/4 bg-red-200'
                    absent++
                }
                if (inOrOut[code]['direction'] === 'In') {
                    classColour = 'text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between w-1/2 sm:w-1/4 bg-green-200'
                    present++
                }
            } else {
                present++
            }

            xxx.push(<div className={classColour} key={loop++}>
                <div className={'mb-1'}>
                    <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance">
                    <img className={'inline-block shadow bg-white border border-gray-600 w-20 lg:w-24 p-1'} src={child.children.personalImage.value} /></a>
                </div>
                <div className="pt-2 font-bold underline mb-1">
                    <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance"
                    className="pt-2 font-bold underline">{child.children.studentName.value}</a>
                </div>
                <div className="mb-1">
                    <div className="text-xxs italic py-2">{child.children.absenceCount.value}</div>
                </div>
                <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                    <div className="flex-1 relative">
                        <Widget columns={1} form={child.children.code} functions={functions} />
                    </div>
                </div>
                <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                    <div className="flex-1 relative">
                        <Widget columns={1} form={child.children.reason} functions={functions} />
                    </div>
                </div>
                <div className="mx-auto float-none w-32 m-0 mb-2 mb-1">
                    <div className="flex-1 relative">
                        <Widget columns={1} form={child.children.comment} functions={functions} />
                        <Widget columns={1} form={child.children.student} functions={functions} />
                    </div>
                </div>
                <div className="mb-1">
                    {getPreviousDaysStatus(child.children.previousDays.value)}
                    {generatePreviousDayElement(child)}
                </div>
            </div>)

        })
        return xxx
    }

    function generatePreviousDayElement(child)
    {
        let days = {...child.children.previousDays}
        days.value = 'Done'

        return (<Widget columns={1} form={days} functions={functions} />)
    }

    function getPreviousDaysStatus(data)
    {
        return Object.keys(data).map(dailyTime => {
            let dates = Object.keys(data[dailyTime]).map(date => {
                let x = data[dailyTime][date]
                let y = ''
                if (x === '') {
                    x = 'highlightNoData w-1/6 float-left text-xxs'
                }
                if (x === 'In') {
                    x = 'highlightPresent w-1/6 float-left text-xxs'
                    y = functions.translate('Present') + ' '
                }
                if (x === 'Out') {
                    x = 'highlightAbsent w-1/6 float-left text-xxs'
                    y = functions.translate('Absent') + ' '
                }
                let dd = date.split(' ')
                return (<div className={x} key={loop++}>
                    <a href="./index.php?q=/modules/Attendance/attendance_take_byPerson.php&amp;gibbonPersonID=0000002746&amp;currentDate=2020-10-19" title={y + date}>
                        {dd[0]}<br />{dd[1]}
                    </a>
                </div>)
            })

            return (<div className="historyCalendarMini" key={loop++}>
                <div className="highlightNoData w-1/6 float-left text-xxs"><span title={dailyTime}>{dailyTime}</span></div>
                {dates}
            </div>)
        })
    }

    function getSaveAttendance()
    {
        let submit = {...form.children[0].children['submit']}
        submit.name = functions.translate('Save Attendance')
        submit.help = ''
        return (<Widget columns={1} functions={functions} form={submit} />)
    }

    function getChangeAllButton()
    {
        let submit = {...form.children[0].children['submit']}
        submit.label = functions.translate('Change All?')
        submit.help = ''
        submit.label_class = ''
        submit.attr = {className: 'button w-32 m-px sm:self-center float-left', title: functions.translate('Change all students to these settings')}
        submit.full_name = 'attendance_by_roll_group[changeAll][submit]'
        submit.id = 'attendance_by_roll_group_changeAll_submit'
        submit.value = 'changeAll'
        let code = {...form.children[0].children['code']}
        code.attr = {className: 'flex float-left'}
        code.full_name = 'attendance_by_roll_group[changeAll][code]'
        code.id = 'attendance_by_roll_group_changeAll_code'
        let reason = {...form.children[0].children['reason']}
        reason.attr = {className: 'flex float-left'}
        reason.placeholder = ' '
        reason.full_name = 'attendance_by_roll_group[changeAll][reason]'
        reason.id = 'attendance_by_roll_group_changeAll_reason'
        let comment = {...form.children[0].children['comment']}
        comment.attr = {className: 'flex float-left'}
        comment.placeholder = ' '
        comment.full_name = 'attendance_by_roll_group[changeAll][comment]'
        comment.id = 'attendance_by_roll_group_changeAll_comment'
        return (
            <div className={'w-3/4 bg-yellow-200'}>
                <Widget columns={1} functions={functions} form={submit} />
                <Widget columns={1} functions={functions} form={code} />
                <Widget columns={1} functions={functions} form={reason} />
                <Widget columns={1} functions={functions} form={comment} />
            </div>)
    }

    return (<div>
        <div className={'w-full flex flex-wrap items-stretch'} id={'react-attendance'} key={loop++}>{getAttendance()}
    </div>
        <div className={'clear-both success text-right w-full border-t border-black'}>{functions.translate('Total students')}: {form.children.length}</div>
        <div className={'text-right font-bold w-full'}>{functions.translate('Total students present in the room')}: {present}</div>
        <div className={'text-right font-bold'}>{functions.translate('Total students absent from the room')}: {absent}</div>
        <div className={'w-full flex flex-wrap items-stretch'}>
            {getChangeAllButton()}
            <div className={'w-1/4'}>
                {getSaveAttendance()}
            </div>
        </div>
    </div>)
}

AttendanceStudentCollection.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

