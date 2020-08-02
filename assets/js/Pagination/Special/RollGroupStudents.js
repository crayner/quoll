'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'

export default class RollGroupStudents extends Component {
    constructor (props) {
        super(props)
        this.row = props.row
        this.content = props.content
        this.functions = props.functions

        this.state = {
            sortOrder: 'rollOrder'
        }
        this.getButtons = this.getButtons.bind(this)
        this.setSort = this.setSort.bind(this)
        this.getContent = this.getContent.bind(this)
        this.sortContent = this.sortContent.bind(this)
    }

    setSort(sort) {
        this.setState({
            sortOrder: sort
        })
    }

    getButtons() {
        let result = []
        let attr = {
            className: 'button warning float-right',
            onClick: () => this.setSort('preferred'),
            key: 'preferred',
            type: 'button',
            style: {margin: '-6px -1px 0 -1px'}
        }
        if (this.state.sortOrder === 'preferred') {
            attr.className = 'button success float-right'
        }
        result.push(<button {...attr}>{this.functions.translate('Preferred Name')}</button>)
        attr = {
            className: 'button warning float-right',
            onClick: () => this.setSort('surname'),
            key: 'surname',
            style: {margin: '-6px -1px 0 -1px'},
            type: 'button'
        }
        if (this.state.sortOrder === 'surname') {
            attr.className = 'button success float-right'
        }
        result.push(<button {...attr}>{this.functions.translate('Surname')}</button>)
        attr = {
            className: 'button warning float-right',
            onClick: () => this.setSort('rollOrder'),
            key: 'rollOrder',
            type: 'button',
            style: {margin: '-6px -1px 0 -1px'},
        }
        if (this.state.sortOrder === 'rollOrder') {
            attr.className = 'button success float-right'
        }
        result.push(<button {...attr}>{this.functions.translate('rollOrder')}</button>)
        return result
    }

    getContent() {
        const content = this.sortContent()
        return content.map(person => {
            person.photo = '/' + person.photo.replace(/^(\/)|(\\)/, "")
            let name = person.reversed_name
            if (this.state.sortOrder === 'preferred') name = person.full_name
            let route = '/person/' + person.person_id + '/edit/Student'
            return <div className={'w-1/2 sm:w-1/3 lg:w-1/5 text-center float-left'} key={person.id}>
                <p className={'text-center text-xs mt-1 font-bold'}>
                <a href={route} title={person.full_name} target={'_blank'}>
                <img src={person.photo} title={person.full_name} className={'user max125 text-center inline'}/>
                    <br />{name}</a></p>
            </div>
        })
    }

    sortContent() {
        let content = this.content
        content.alphaSort(this.state.sortOrder)
        return content
    }


    render () {
        console.log(this)
        return (<div>
            <h3>{this.functions.translate('Sort By')}
                {this.getButtons()}
            </h3>
            <h3>{this.functions.translate('Students')}</h3>
            {this.getContent()}
        </div>)
    }
}

RollGroupStudents.propTypes = {
    row: PropTypes.object.isRequired,
    content: PropTypes.array.isRequired,
    functions: PropTypes.object.isRequired,
}

Array.prototype.alphaSort = function (sortOrder) {
    function compare(a, b) {
        if (a.rollOrder === null) a.rollOrder = '0'
        if (b.rollOrder === null) b.rollOrder = '0'
        let a1 = a.rollOrder.padStart(5,'0') + a.reversed_name
        let b1 = b.rollOrder.padStart(5,'0') + b.reversed_name
        if (sortOrder === 'surname') {
            a1 = a.reversed_name
            b1 = b.reversed_name
        }
        if (sortOrder === 'preferred') {
            a1 = a.full_name
            b1 = b.full_name
        }
        console.log(sortOrder)
        if (a1 > b1) return 1
        if (a1 < b1) return -1
        return 0
    }
    this.sort(compare);
}