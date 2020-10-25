'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import CollectionRows from '../Template/Table/CollectionRows'
import Widget from '../Widget'

export default function AttendanceStudentCollection(props) {
    const {
        functions,
        form,
    } = props

    function getAttendance()
    {
        let loop = 0
        return Object.keys(form.children).map(key => {
            let child = form.children[key]

            return (<div className={'text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between w-1/2 sm:w-1/4'} key={loop++}>
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
                </div>
            </div>)
        })
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
                return (<div className={x}>
                    <a href="./index.php?q=/modules/Attendance/attendance_take_byPerson.php&amp;gibbonPersonID=0000002746&amp;currentDate=2020-10-19" title={y + date}>
                        {dd[0]}<br />{dd[1]}
                    </a>
                </div>)
            })

            return (<div className="historyCalendarMini">
                <div className="highlightNoData w-1/6 float-left text-xxs"><span title={dailyTime}>{dailyTime}</span></div>
                {dates}
            </div>)
        })
    }

    return (<div className={'w-full flex flex-wrap items-stretch'} id={'react-attendance'}>{getAttendance()}</div> )
}

AttendanceStudentCollection.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

