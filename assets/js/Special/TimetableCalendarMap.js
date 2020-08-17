'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { fetchJson } from '../component/fetchJson'
import Messages from '../component/Messages'

export default class TimetableCalendarMap extends Component {
    constructor (props) {
        super(props)
        this.firstDayOfTheWeek = props.firstDayOfTheWeek === 1 ? 1 : 0
        this.days = props.days
        this.locale = props.locale.replace('_','-')
        this.timetable = props.timetable
        this.functions = props.functions
        this.translations = props.functions.mergeTranslations(props.translations)
        this.state = {
            weeks: props.weeks,
            messages: [],
        }
    }

    headerRow() {
        let result = [];
        result.push(<div className={"text-sm w-1/8 float-left py-3 border border-gray-800 bg-gray-400 text-center"} key={'stuff'}>{this.translations['Week Number']}</div>)
        for (let i = this.firstDayOfTheWeek; i < this.firstDayOfTheWeek + 7; i++) {
            result.push(<div className={"text-sm w-1/8 float-left py-3 text-center border border-gray-800 bg-gray-400"} key={i}>{this.translations[this.days[i]]}</div>)
        }

        return (<div className={'headerRow font-bold'}>{result}</div>)
    }

    weeksContent() {
        return this.state.weeks.map((week,key) => {
            let result = []
            result.push(<div className={"w-1/8 float-left py-3 border border-gray-800 text-center h-32 align-middle"} key={'wn'}>{week.number}</div>)
            for (let i = this.firstDayOfTheWeek; i < this.firstDayOfTheWeek + 7; i++) {
                let day = null
                week.days.map(w => {
                    if (day === null && (w.dayOfWeek === i || (w.dayOfWeek === 7 && i === 0))) {
                        day = w
                    }
                })
                let datum = []
                let className = "text-sm w-1/8 float-left py-3 text-center border border-gray-800 h-32"
                if (day !== null && day.schoolDay) {
                    datum.push(<p className={'text-center text-xs m-0'} key={'date'}>{new Intl.DateTimeFormat(this.locale, {
                        year: "numeric",
                        month: "short",
                        day: "2-digit"
                    }).format(new Date(day.date))}</p>)
                    if (day.specialDay !== null) {
                        if (day.specialDay.type === 'School Closure') {
                            className += ' bg-gray-400'
                        }
                        datum.push(<p className={'text-center text-xs m-0'} key={'special'}>{day.specialDay.name}</p>)
                    } else if (!day.schoolOpen) {
                        className += ' bg-gray-400'
                    } else {
                        datum.push(<p className={'text-center text-xs m-0'} key={'name'}>{this.translations['School Day']}</p>)

                        let buttons = []
                        buttons.push(<a className={'thickbox'} title={this.translations['Next Timetable Day']} key={0} onClick={() => this.nextColumn(day.date)}>
                            <span className={'fas fa-step-forward fa-1-5x fa-fw text-gray-800 hover:text-orange-500'} /></a>)
                        if (!day.dayDate.timetableDay.isFixed) {
                            buttons.push(<a className={'thickbox'} title={this.translations['Ripple Timetable Days in Term']}
                                            key={1} onClick={() => this.rippleColumns(day.date)}>
                                <span className={'fas fa-sync fa-1-5x fa-fw text-gray-800 hover:text-indigo-500'}/></a>)
                        }

                        datum.push(<p className={'text-center text-xs m-1 mb-2'} key={'buttons'}>{buttons}</p>)
                        datum.push(<p className={'text-center text-xs mx-0 mb-0 mt-1 font-bold'} key={'column'}><span className={'p-1 mt-1'} style={{color: day.dayDate.timetableDay.fontColour, backgroundColor: day.dayDate.timetableDay.colour}}>{day.dayDate.timetableDay.name}</span></p>)
                    }
                }
                result.push(<div className={className} key={i}>{datum}</div>)
            }
            return (<div className={'week'} key={week.number}>{result}</div>)
        })
    }

    nextColumn(date) {
        let url = '/timetable/{timetable}/calendar/next/{date}/column/'
        return this.columnUpdate(date, url)
    }

    columnUpdate(date, url) {
        url = url.replace('{timetable}', this.timetable).replace('{date}',date)
        this.setState({
            messages: [{class: 'info', message: 'Let me ponder your request'}]
        })
        fetchJson(
            url,
            [],
            false
        ).then(data => {
            this.setState({
                weeks: data.weeks,
                messages: data.errors,
            })
        })
    }

    rippleColumns(date) {
        let url = '/timetable/{timetable}/calendar/ripple/{date}/columns/'
        return this.columnUpdate(date, url)
    }

    render () {
        return (<div><Messages messages={this.state.messages} translate={this.functions.translate} />{this.headerRow()}{this.weeksContent()}</div>)
    }
}

TimetableCalendarMap.propTypes = {
    firstDayOfTheWeek: PropTypes.number.isRequired,
    translations: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    days: PropTypes.object.isRequired,
    weeks: PropTypes.array.isRequired,
    locale: PropTypes.string.isRequired,
    timetable: PropTypes.string.isRequired,
}
TimetableCalendarMap.defaultProps = {
}
