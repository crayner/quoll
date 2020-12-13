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
    let inOrOut = form.special_data.inOrOut
    let dateChoiceName = form.special_data.dateChoiceName
    let dateChoices = form.special_data.dateChoices

    let originalStudents = form.children

    function insertDateChoiceName(child, index, name)
    {
        child = {...child}
        child.full_name = child.full_name.replace('[' + name + '][' + (index-1) + ']', '')
        child.id = child.id.replace('_' + name + '_' + (index-1), '')
        child.full_name = child.full_name.replace('[students]', '[' + name + '][' + index + '][students]')
        child.id = child.id.replace('_students', '_' + name + '_' + index + '_students')
        return child
    }

    function insertDateCollection(form, name)
    {
        if (dateChoices.length <= 1) {
            dateChoices = [{'id': 'default', 'value': 0, 'label': 'Default'}]
        }
        form.full_name = form.full_name.replace('[students]', '[' + name + ']')
        form.id = form.id.replace('_students', '_' + name)
        form.name = name
        form.students = []

        dateChoices.map(dateChoice => {
            let index = dateChoice.value
            originalStudents.map(student => {
                student = {...student}
                let newStudent = {}
                Object.keys(student.children).map(key => {
                    let child = insertDateChoiceName(student.children[key], index, name)
                    if (child.name === 'dateChoice' && dateChoices.length <= 1) {
                        child.type = 'hidden'
                        child.value = 0
                    } else if (child.name === 'dateChoice' && dateChoices.length > 1) {
                        child.value = 0
                        child.choices = dateChoices
                    }
                    newStudent[key] = child
                })
                student.children = newStudent
                form.students.push(insertDateChoiceName(student, index, name))
            })

        })
        return form
    }

    function getDateChoiceContent(dateChoice)
    {
        if (dateChoice.choices.length <= 1) {
            dateChoice.type = 'hidden'
            dateChoice.value = 0
            return (<Widget columns={1} form={dateChoice} functions={functions} />)
        }

        let choice = dateChoice.id.substr(dateChoice.id.indexOf(dateChoiceName) + dateChoiceName.length + 1, 1)
console.log(choice,dateChoice.value,choice != dateChoice.value)
        if (choice != dateChoice.value) {
            dateChoice.type = 'hidden'
            return (<Widget columns={1} form={dateChoice} functions={functions} />)
        }
        return (<div className="mx-auto float-none w-32 m-0 mb-px mb-1">
            <div className="flex-1 relative">
                <Widget columns={1} form={dateChoice} functions={functions} />
            </div>
        </div>)
    }

    function getAttendance()
    {
        let z = 0
        let xxx = []
        let students = insertDateCollection(form, dateChoiceName)

        students.students.map(child => {
            let student = {...child.children}
            let code = student.code.value !== '' ? student.code.value : form.special_data.default_code
            student.code.value = code
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

            if (typeof student.previousDays === 'undefined') {
                xxx.push(<div className={classColour} key={loop++}>
                    <div className={'mb-1'}>
                        <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance">
                            <img className={'inline-block shadow bg-white border border-gray-600 w-20 lg:w-24 p-1'}
                                 src={student.personalImage.value}/></a>
                    </div>
                    <div className="pt-2 font-bold underline mb-1">
                        <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance"
                           className="pt-2 font-bold underline">{student.studentName.value}</a>
                    </div>
                    <div className="mb-1">
                        <div className="text-xxs italic py-2">{student.absenceCount.value}</div>
                    </div>
                    {getDateChoiceContent(student.dateChoice)}
                    <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.code} functions={functions}/>
                        </div>
                    </div>
                    <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.reason} functions={functions}/>
                        </div>
                    </div>
                    <div className="mx-auto float-none w-32 m-0 mb-2 mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.comment} functions={functions}/>
                            <Widget columns={1} form={student.student} functions={functions}/>
                        </div>
                    </div>
                </div>)
            } else {
                xxx.push(<div className={classColour} key={loop++}>
                    <div className={'mb-1'}>
                        <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance">
                            <img className={'inline-block shadow bg-white border border-gray-600 w-20 lg:w-24 p-1'}
                                 src={student.personalImage.value}/></a>
                    </div>
                    <div className="pt-2 font-bold underline mb-1">
                        <a href="index.php?q=/modules/Students/student_view_details.php&amp;gibbonPersonID=0000002746&amp;subpage=Attendance"
                           className="pt-2 font-bold underline">{student.studentName.value}</a>
                    </div>
                    <div className="mb-1">
                        <div className="text-xxs italic py-2">{student.absenceCount.value}</div>
                    </div>
                    {getDateChoiceContent(student.dateChoice)}
                    <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.code} functions={functions}/>
                        </div>
                    </div>
                    <div className="mx-auto float-none w-32 m-0 mb-px mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.reason} functions={functions}/>
                        </div>
                    </div>
                    <div className="mx-auto float-none w-32 m-0 mb-2 mb-1">
                        <div className="flex-1 relative">
                            <Widget columns={1} form={student.comment} functions={functions}/>
                            <Widget columns={1} form={student.student} functions={functions}/>
                        </div>
                    </div>
                    <div className="mb-1">
                        {getAttendanceStatus(student.previousDays.value, functions)}
                        {generatePreviousDayElement(child)}
                    </div>
                </div>)
            }

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

