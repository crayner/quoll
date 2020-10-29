'use strict'

import React from 'react'

export function AttendanceSummaryStatus(data, functions)
{
     return (<div>{getAttendanceStatus(data, functions)}</div>)
}

export function getAttendanceStatus(data, functions)
{
    let loop = 0
    return Object.keys(data).map(dailyTime => {
        let dates = Object.keys(data[dailyTime]).map(date => {
            let x = data[dailyTime][date]
            let y = ''
            let z = 'highlightNoData'

            if (x.direction === 'In') {
                z = 'highlightPresent'
                y = functions.translate('Present') + ' '
            }
            if (x.direction === 'Out') {
                z = 'highlightAbsent'
                y = functions.translate('Absent') + ' '
            }
            let theDate = new Date(date)
            let dd = [theDate.getDate(), functions.translate('month.short.' + theDate.getMonth())]
            let title = '' + y + ' ' + dd[0] + ' ' + dd[1]
            title.trim
            return (<div className={z} key={loop++}>
                <a href={x.href} title={title}>
                    {dd[0]}<br />
                    {dd[1]}
                </a>
            </div>)
        })

        let calendarWidth = 1.25 * Object.keys(data[dailyTime]).length + 1.5
        calendarWidth.toString
        let calendarStyle = {width: calendarWidth + 'rem'}

        return (<div className="historyCalendarMini craig" key={loop++} style={calendarStyle} >
            <div className="highlightNoData"><span title={dailyTime}>{dailyTime}</span></div>
            {dates}
        </div>)
    })
}


