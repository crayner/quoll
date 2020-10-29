'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Widget from '../Widget'
import { getAttendanceStatus } from './AttendanceSummaryStatus'

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
        let inOrOut = form.children[0].children['inOrOut'].value

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
                    {getAttendanceStatus(child.children.previousDays.value, functions)}
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

    return (<div>
        <div className={'w-full flex flex-wrap items-stretch'} id={'react-attendance'} key={loop++}>{getAttendance()}</div>
        <div className={'clear-both success text-right w-full border-t border-black'}>{functions.translate('Total students')}: {form.children.length}</div>
        <div className={'text-right font-bold w-full'}>{functions.translate('Total students present in the room')}: {present}</div>
        <div className={'text-right font-bold'}>{functions.translate('Total students absent from the room')}: {absent}</div>
    </div>)
}

AttendanceStudentCollection.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}

