'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import HeaderRow from "./HeaderRow"
import firstBy from 'thenby'
import PaginationContent from "./PaginationContent"
import PaginationFilter from "./PaginationFilter"
import PaginationSearch from "./PaginationSearch"
import AreYouSureDialog from "../component/AreYouSureDialog"
import InformationDetail from "../component/InformationDetail"
import {fetchJson} from "../component/fetchJson"
import {openUrl} from "../Container/ContainerFunctions"
import Messages from "../component/Messages"
import { isEmpty } from '../component/isEmpty'
import RollGroupStudents from './Special/RollGroupStudents'
import SelectedRowForm from './SelectedRowForm'

export default class PaginationApp extends Component {
    constructor (props) {
        super(props)
        this.pageMax = props.pageMax
        this.row = props.row
        this.group = props.group
        this.content = props.content
        this.filters = props.row.filters
        this.messages = props.functions.mergeTranslations(props.translations)
        this.search = props.row.search
        this.filterGroups = props.row.filterGroups
        this.contentLoader = props.contentLoader
        this.defaultFilter = props.row.defaultFilter
        this.initialFilter = props.initialFilter
        this.initialSearch = props.initialSearch
        this.addElementRoute = props.addElementRoute
        this.returnRoute = props.returnRoute
        this.refreshRoute = props.refreshRoute
        this.draggableSort = props.draggableSort
        this.columnCount = 0
        this.storeFilterURL = props.storeFilterURL
        this.draggableRoute = props.draggableRoute
        this.storeFilterWait = true
        this.functions = props.functions
        this.functions.handleAddClick = typeof this.functions.handleAddClick === 'function' ? this.functions.handleAddClick : this.handleAddClick.bind(this)
        this.functions.sortColumn = this.sortColumn.bind(this)
        this.functions.toggleAll = this.toggleAll.bind(this)
        this.functions.toggleSelectedRow = this.toggleSelectedRow.bind(this)
        this.functions.manageSelectedRowAction = this.manageSelectedRowAction.bind(this)
        this.functions.deleteItem = this.deleteItem.bind(this)
        this.functions.areYouSure = this.areYouSure.bind(this)
        this.functions.loadNewPage = this.loadNewPage.bind(this)
        this.functions.displayInformation = this.displayInformation.bind(this)
        this.functions.dropEvent = this.dropEvent.bind(this)
        this.functions.headerRow = this.headerRow.bind(this)
        this.functions.callSelectedRowAction = this.callSelectedRowAction.bind(this)
        this.paginationContext = typeof props.context === 'array' ? {} : props.context


        this.sortColumn = this.sortColumn.bind(this)
        this.firstPage = this.firstPage.bind(this)
        this.lastPage = this.lastPage.bind(this)
        this.prevPage = this.prevPage.bind(this)
        this.nextPage = this.nextPage.bind(this)
        this.closeConfirm = this.closeConfirm.bind(this)
        this.adjustPageSize = this.adjustPageSize.bind(this)
        this.changeFilter = this.changeFilter.bind(this)
        this.changeSearch = this.changeSearch.bind(this)
        this.clearSearch = this.clearSearch.bind(this)
        this.translate = props.functions.translate
        this.handleAddClick = this.handleAddClick.bind(this)

        this.path = ''

        this.state = {
            sortColumn: '',
            sortDirection: '',
            results: [],
            offset: 0,
            pageMax: this.pageMax,
            confirm: false,
            information: false,
            filteredContent: this.content,
            filter: [],
            search: '',
            messages: [],
            selectAllRows: false,
            rowsSelected: false,
            waiting: false,
            confirmOptions: {
                function: 'deleteItem',
                options: {path: this.path}
            }
        }
    }

    componentDidMount() {
        this.columnCount = 0
        this.row.columns.map(column => {
            if (column.dataOnly === false)
                this.columnCount = this.columnCount + 1
        })

        if (this.row.actions.length > 0)
            this.columnCount = this.columnCount + 1

        if (this.contentLoader !== false) {
            this.row.emptyContent = this.messages['Loading Content...']
            this.loadContent()
        } else {
            this.storeFilterWait = false
        }

        this.changeFilter(null)

        if (this.draggableSort) {
            let info = {}
            info.class = 'info'
            info.message = 'Items rows can be ordered by dragging onto another item, inserting above that item when dropped.'
            info.close = false
            this.setState({
                messages: [info],
            })
        }

        this.setInitialFilter()

        if (this.initialSearch !== '') {
            let x = {}
            x.target = {}
            x.target.value = this.initialSearch
            this.changeSearch(x)
        }
    }

    setInitialFilter() {
        if (this.initialFilter.length > 0) {
            this.initialFilter.map(value => {
                this.changeFilter(this.filters[value.name])
            })
        }
    }

    loadContent() {
        fetchJson(this.contentLoader, {}, false)
            .then(data => {
                if (data.status === 'success'){
                    this.content = data.content
                    this.pageMax = data.pageMax
                    let result = this.paginateContent(this.content,0, this.pageMax)
                    this.row.emptyContent = this.messages['There are no records to display.']
                    this.setState({
                        results: result,
                        filteredContent: this.content
                    })
                    this.setInitialFilter()
                    if (this.initialSearch !== '') {
                        let x = {}
                        x.target = {}
                        x.target.value = this.initialSearch
                        this.changeSearch(x)
                    } this.storeFilterWait = false
                } else {
                    this.setState({
                        messages: [data]
                    })
                }
            }).catch(error => {
                console.error(error)
                this.setState({
                    information: error
                })
        })
    }

    areYouSure(path) {
        this.setState({
            confirm: true,
            confirmOptions: {function: 'deleteItem', options: {path: path, target: '_self'}}
        })
    }

    loadNewPage(path, options) {
        if (typeof path === 'object' && path.url) {
            window.open(path.url, path.target, path.options)
            return
        }
        if (typeof options.options !== 'string') {
            options.options = '_self'
        }
        window.open(path, options.options)
    }

    displayInformation(path) {
        this.path = path
        this.setState({
            information: {header: this.translate('Loading') + '...', 'content': ''},
        })
        fetchJson(
            path,
            {},
            false
        ).then(data => {
            this.setState({
                information: data,
            })
        })
    }

    deleteItem(options) {
        console.log(options)
        let path = options.path
        this.setState({
            confirm: false,
            confirmOptions: {},
        })
        this.functions.getContent(path,'_self')
    }

    closeConfirm(){
        this.path = ''
        this.setState({
            confirm: false,
            confirmOptions: {},
            information: false
        })
    }

    dropEvent(ev) {
        ev.preventDefault()
        let data = ev.dataTransfer.getData("text")
        let source = data.replace('pagination', '')
        let target = ev.target.parentNode.id.replace('pagination', '')
        if (source === target || source === '' || target === '') {
            if (source === '' || target === '') {
                let errors = this.state.messages
                errors.push({
                    class: 'warning',
                    message: this.translate('When dropping an item, ensure that the entire row is selected.')
                })
                this.setState({
                    messages: errors
                })
            }
            return
        }

        let route = this.draggableRoute.replace('__source__', source).replace('__target__',target)
        fetchJson(route,
            {},
            false).then(data => {
                let errors = this.state.messages
                data.errors.map(error => {
                    errors.push(error)
                })
                if (data.status === 'success') {
                    this.content = data.content
                    let result = this.paginateContent(this.content,0, this.state.pageMax)
                    this.setState({
                        results: result,
                        messages: errors,
                    })
                } else if (data.status === 'error') {
                    this.setState({
                        messages: errors,
                    })
                }
        })
    }

    sortColumn(columnName){
        let column = {}
        Object.keys(this.row.columns).filter(columnKey=> {
            if(this.row.columns[columnKey].contentKey === columnName)
                column = this.row.columns[columnKey]
        })
        if (column.sort !== true)
            return

        let direction = this.state.sortDirection

        if (typeof columnName === 'object')
            columnName = columnName[0]

        if (columnName === this.state.sortColumn)
        {
            direction = direction === 'down' ? 'up' : 'down'
        } else {
            direction = 'down'
        }

        let result = this.paginateContent(this.sortContent(columnName,direction,this.state.filteredContent), this.state.offset)
        this.setState({
            sortColumn: columnName,
            sortDirection: direction,
            results: result,
            control: this.buildControl(this.state.offset, result)
        })
    }

    sortContent(name, direction, content)
    {
        if (name === '') {
            name = this.state.sortColumn
            direction = this.state.sortDirection
        }
        if (name === '')
            return content

        return content.sort(
            firstBy(name, direction === 'up' ? -1 : 1)
        )
    }

    paginateContent(content, offset, pageMax = 0) {
        if (pageMax === 0) {
            pageMax = this.state.pageMax
        }
        if (typeof content === 'object') {
            let loop = -1;
            let result = []
            Object.keys(content).filter(key => {
                loop++
                if (loop >= offset && loop < offset + pageMax) {
                    result.push(content[key])
                }
            })
            return result
        } else {
            console.error('The content MUST be an array (object)!')
        }
    }

    firstPage(){
        this.checkOffset(this.state.filteredContent,0)
    }

    prevPage() {
        this.checkOffset(this.state.filteredContent,this.state.offset - this.state.pageMax)
    }

    nextPage() {
        this.checkOffset(this.state.filteredContent,this.state.offset + this.state.pageMax)
    }

    lastPage() {
        let offset = this.state.offset
        while (offset <= this.state.filteredContent.length)
            offset = offset + this.state.pageMax
        this.checkOffset(this.state.filteredContent, offset, this.state.pageMax)
    }

    handleAddClick(e, file, target) {
        openUrl(file,target)
    }

    checkOffset(filteredContent, offset, pageMax = 0) {
        if (pageMax === 0)
            pageMax = this.state.pageMax

        while (offset > filteredContent.length)
            offset = offset - pageMax

        if (pageMax >= filteredContent.length)
            offset = 0

        if (offset < 0)
            offset = 0

        let result = this.paginateContent(this.sortContent('', '', filteredContent), offset, pageMax)

        this.setState({
            offset: offset,
            results: result,
            pageMax: pageMax,
            sizeButtons: this.buildPageSizeControls(pageMax),
            filteredContent: filteredContent,
        })
    }

    adjustPageSize(size) {
        if (size === 'All')
            size = this.state.filteredContent.length

        this.checkOffset(this.state.filteredContent,this.state.offset, size)
    }

    buildPageSizeControls() {
        let control = []
        if (this.state.filteredContent.length > 10) {
            control.push(<a key={'10'} onClick={() => this.adjustPageSize(10)} className={(this.state.pageMax === 10 ? 'text-blue-600 pointer-hover font-bold' : 'text-gray-600 pointer-hover')}>10</a>)
            control.push(<a key={'25'} onClick={() => this.adjustPageSize(25)} className={(this.state.pageMax === 25 ? 'text-blue-600 pointer-hover font-bold' : 'text-gray-600 pointer-hover')}>,25</a>)
        }
        if (this.state.filteredContent.length > 25)
            control.push(<a key={'50'} onClick={() => this.adjustPageSize(50)} className={(this.state.pageMax === 50 ? 'text-blue-600 pointer-hover font-bold' : 'text-gray-600 pointer-hover')}>,50</a>)
        if (this.state.filteredContent.length > 50)
            control.push(<a key={'100'} onClick={() => this.adjustPageSize(100)} className={(this.state.pageMax === 100 ? 'text-blue-600 pointer-hover font-bold' : 'text-gray-600 pointer-hover')}>,100</a>)
        if (this.state.filteredContent.length > 100)
            control.push(<a key={'All'} onClick={() => this.adjustPageSize('All')} className={(this.state.pageMax === this.state.filteredContent.length ? 'text-blue-600 pointer-hover font-bold' : 'text-gray-600 pointer-hover')}>,{this.messages['All']}</a>)

        return control
    }

    buildControl() {
        let control = []
        if (this.state.filteredContent.length > 0) {

            let content = this.row.caption.replace('{start}', (this.state.offset + 1))
            content = content.replace('{end}', (this.state.results.length + this.state.offset))
            content = content.replace('{total}', this.state.filteredContent.length)

            if (this.state.offset > 0) {
                control.push(<a key={'first'} onClick={() => this.firstPage()}
                                title={this.row.firstPage}><span
                    className={'float-left text-gray-600 fas fa-angle-double-left fa-fw pointer-hover pt-1 pb-2 pr-1 hover:text-blue-600'}/></a>)
            }

            if (this.state.filteredContent.length > this.state.pageMax && this.state.offset > 0) {
                control.push(<a key={'prev'} onClick={() => this.prevPage()} title={this.row.prevPage}><span
                    className={'float-left text-gray-600 fas fa-angle-left fa-fw pointer-hover pt-1 pb-2 pr-1 hover:text-blue-600'}/></a>)
            }

            control.push(<div className={'float-left pt-1 pb-2 pr-2 pl-2 font-bold'} key={'content'}>{content}</div>)

            if (this.state.filteredContent.length > this.state.pageMax && this.state.pageMax + this.state.offset < this.state.filteredContent.length) {
                control.push(<a key={'next'} onClick={() => this.nextPage()} title={this.row.nextPage}><span
                    className={'text-gray-600 fas fa-angle-right fa-fw pointer-hover pt-1 pb-2 pl-1 hover:text-blue-600'}/></a>)
                control.push(<a key={'last'} onClick={() => this.lastPage()} title={this.row.lastPage}><span
                    className={'text-gray-600 fas fa-angle-double-right fa-fw pointer-hover pt-1 pb-2 pl-1 hover:text-blue-600'}/></a>)
            }
        }
        if (this.returnRoute !== null) {
            control.push(<a key={'remove'} className={'close-button gray ml-3'} onClick={() => this.functions.handleAddClick(this.returnRoute, '_self')} title={this.row.returnPrompt}><span className={'fas fa-reply fa-fw text-gray-800 hover:text-indigo-500'}/></a>)
        }
        if (this.refreshRoute !== null) {
            control.push(<a key={'refresh'} className={'close-button gray ml-3'} onClick={() => this.functions.handleAddClick(this.refreshRoute, '_self')} title={this.row.refreshPrompt}><span className={'fas fa-sync fa-fw text-gray-800 hover:text-indigo-500'}/></a>)
        }
        if (this.addElementRoute !== null) {
            control.push(<a key={'add'} className={'close-button gray ml-3'} onClick={() => this.functions.handleAddClick(this.addElementRoute, '_self')} title={this.row.addElement}><span className={'fas fa-plus-circle fa-fw text-gray-800 hover:text-indigo-500'}/></a>)
        }
        return control
    }

    filterContent(filter, search) {
        if (isEmpty(filter))
            filter = this.state.filter
        let content = this.content
        filter.map(filterValue => {
            const filterDetail = this.filters[filterValue.name]
            content = content.filter(value => {
                if (typeof filterDetail.value === 'object') {
                    if (filterDetail.value.includes(value[filterDetail.contentKey]))
                        return value
                }
                if (filterDetail.value === value[filterDetail.contentKey])
                    return value

                if (filterDetail['softMatch']) {
                    if (typeof value[filterDetail.contentKey] !== 'string') {
                        console.error('The filter value is not suitable for a soft match. Set the filter to hard match.')
                    }

                    if (value[filterDetail.contentKey].includes(filterDetail.value))
                        return value
                }
            })
        })

        if (this.search && search !== '') {
            search = search.toLowerCase()
            let filtered = []
            Object.keys(content).filter(xxx => {
                let value = content[xxx]
                let selected = false
                this.row.columns.map(column => {
                    if (column.search) {
                        column.contentKey.map(key => {
                            if (!selected && typeof value[key] === 'string' && value[key].toLowerCase().includes(search)) {
                                selected = true
                                filtered.push(value)
                            }
                            if (!selected && typeof value[key] === 'number' && ('' + value[key]).toLowerCase().includes(search)) {
                                selected = true
                                filtered.push(value)
                            }
                        })
                    }
                })
            })
            content = filtered
        }
        return content
    }

    changeFilter(value) {
        if (value === null || typeof value === 'undefined') {
            if (this.defaultFilter === null) {
                let result = this.paginateContent(this.content,this.state.offset)
                this.setState({
                    results: result,
                    control: this.buildControl(this.state.offset, result),
                    sizeButtons: this.buildPageSizeControls(this.state.pageMax),
                    confirm: false,
                })
                return
            }
        } else if (typeof value === 'object' && typeof value.group === 'undefined') {
            if (value.target.value === '')
                return
            if (typeof value.target.value === 'object' && typeof value.target.value.value !== 'undefined') {
                value = this.filters[value.target.value.value]
            } else if (typeof value.target.value === 'string') {
                value = this.filters[value.target.value]
            }
        }
        let filter = this.state.filter

        if (this.filterGroups) {
            // Turn off a filter that is set.
            let existed = false
            let newFilter = []
            filter.map(x => {
                if (x.name === value.name) {
                    existed = true
                    return
                }
                if (x.group === value.group) {
                    return
                }
                newFilter.push(x)
            })

            // Add a new filter.
            if (existed === false) {
                newFilter.push(value)
            }

            if (newFilter.length > 0 && !(newFilter[0] === null || typeof newFilter[0] == 'undefined')) {
                filter = newFilter.filter(x => {
                    if (x.group !== value.group) {
                        return x
                    }
                    if (value.name === x.name) {
                        return x
                    }
                })
            } else {
                filter = []
            }

            if (this.defaultFilter !== null) {
                let filterGroups = {}
                filter.map(x => {
                    filterGroups[x.group] = x
                })
                Object.keys(this.defaultFilter).map(key => {
                    let x = this.defaultFilter[key]
                    if (typeof filterGroups[x.group] === 'undefined') {
                        filter.push(x)
                    }
                })
            }
        } else {
            filter = [value]
        }
        const filteredContent = this.filterContent(filter, this.state.search)
        let result = this.paginateContent(this.sortContent('', '', filteredContent), 0, 0)
        this.setState({
            results: result,
            control: this.buildControl(this.state.offset, result),
            filter: filter,
            filteredContent: filteredContent,
        })
    }

    changeSearch(e) {
        const filteredContent = this.filterContent(this.state.filter, e.target.value)

        let results = this.paginateContent(this.sortContent('', '', filteredContent), 0, 0)

        this.setState({
            search: e.target.value,
            results: results,
            control: this.buildControl(this.state.offset, results),
            filteredContent: filteredContent
        })
    }

    clearSearch() {
        const filteredContent = this.filterContent(this.state.filter, '')

        let results = this.paginateContent(this.sortContent('', '', filteredContent), 0, 0)

        this.setState({
            search: '',
            results: results,
            control: this.buildControl(this.state.offset, results),
            filteredContent: filteredContent
        })
    }

    searchFilter() {
        if (this.search || this.state.rowsSelected)
            return ''
        if (Object.keys(this.filters).length > 0)
            return ''
        return 'none'
    }

    storeFilter()
    {
        if ('' === this.storeFilterURL || null === this.storeFilterURL || this.storeFilterWait)
            return
        let data = {}
        data.filter = this.state.filter
        data.search = this.state.search
        this.storeFilterWait = true
        fetchJson(
            this.storeFilterURL,
            {method: 'POST', body: JSON.stringify(data)},
            false
        ).then(data => {
            this.storeFilterWait = data === 'craig'
        })
    }

    headerRow(show)
    {
        if (this.group.name === '' || show) {
            return (
                <thead>
                    <HeaderRow
                        row={this.row}
                        sortColumn={this.sortColumn}
                        sortColumnName={this.state.sortColumn}
                        sortColumnDirection={this.state.sortDirection}
                        functions={this.functions}
                        selectAllRows={this.state.selectAllRows} />
                </thead>
            )
        }
        return null
    }

    toggleAll() {
        const x = !this.state.selectAllRows
        let content = Object.keys(this.state.filteredContent).map(index => {
            let row = this.state.filteredContent[index]
            row.selected = x
            return row
        })

        this.setState({
            selectAllRows: x,
            rowsSelected: x,
            filteredContent: content,
        })
    }

    toggleSelectedRow(contentRow) {
        contentRow.selected = !contentRow.selected
        let rowsSelected = false
        let content = Object.keys(this.state.filteredContent).map(index => {
            let row = this.state.filteredContent[index]
            if (row.id === contentRow.id) {
                if (contentRow.selected) rowsSelected = true
                return contentRow
            }
            if (row.selected) rowsSelected = true
            return row
        })

        this.setState({
            filteredContent: content,
            rowsSelected: rowsSelected,
        })
    }

    manageSelectedRowAction(event) {
        let data = []
        Object.keys(this.state.filteredContent).map(index => {
            let item = this.state.filteredContent[index]
            if (item.selected) {
                data.push(item)
            }
        })
        let body = {
            selected: data,
            _token: this.row.token,
        }
        let url = event.target.value

        this.setState({
            confirm: true,
            confirmOptions: {
                function: 'callSelectedRowAction',
                options: {url: url, body: body}
            },
        })
    }

    callSelectedRowAction(options) {
        this.setState({
            waiting: true,
        })
        fetchJson(
            options['url'],
            { method: 'POST', body: JSON.stringify(options['body']) },
            false
        ).then(data => {
            if (data.status === 'redirect') window.open(data.redirect, '_self')

            let content = Object.keys(this.state.filteredContent).map(index => {
                let item = this.state.filteredContent[index]
                item.selected = false
                return item
            })
            this.setState({
                messages: data.errors,
                filteredContent: content,
                selectAllRows: false,
                rowsSelected: false,
                waiting: false,
                confirmOptions: {
                    function: 'deleteItem',
                    options: {path: this.path}
                },
            })
        })
    }

    doIt() {
        let options = {...this.state.confirmOptions}
        this.setState({
            confirm: false,
            confirmOptions: {}
        })
        if (this.path !== '') {
            options.path = this.path
            options.function = 'deleteItem'
            options.options = {}
            this.path = ''
        }
        return this.functions[options.function](options.options)
    }

    render () {
        this.storeFilter()

        if (this.row.special !== false) {
            if (this.row.special === 'Roll Group Students') {
                return <RollGroupStudents row={this.row} content={this.content} functions={this.functions} />
            }
        }

        let selectedRowForm = null
        if (this.row.selectRow && this.state.rowsSelected) {
            selectedRowForm = (<SelectedRowForm functions={this.functions} row={this.row} messages={this.messages} />)
        }

        return (
            <div className={'paginationApp'}>
                <table className={'noIntBorder fullWidth relative'} style={{display: this.searchFilter()}}>
                    <tbody>
                        <PaginationSearch search={this.search} messages={this.messages} clearSearch={this.clearSearch} changeSearch={this.changeSearch} searchValue={this.state.search} />
                        <PaginationFilter filter={this.state.filter} filters={this.filters} changeFilter={this.changeFilter} messages={this.messages} defaultFilters={this.defaultFilter !== null} />
                        {selectedRowForm}
                    </tbody>
                </table>
                <div className={'text-xs text-gray-600 text-left paginationControls'}>
                    <span className={'float-left clear-both'}>{this.buildPageSizeControls()}</span>
                    <span className={'float-right'}>{this.buildControl()}</span>
                </div>
                <Messages messages={this.state.messages} translate={this.translate} />
                {this.state.waiting ? <div className={'waitOne info'}>{this.functions.translate('Let me ponder your request')}...</div> : ''}
                <table className={'w-full striped'}>
                    {this.headerRow(false)}
                    <PaginationContent row={this.row} group={this.group} content={this.state.results} functions={this.functions} draggableSort={this.draggableSort} selectAllRows={this.state.selectAllRows} />
                </table>
                <AreYouSureDialog messages={this.messages} doit={() => this.doIt()} cancel={() => this.closeConfirm()} confirm={this.state.confirm} />
                <InformationDetail messages={this.messages} cancel={() => this.closeConfirm()} information={this.state.information} />
            </div>
        )
    }
}

PaginationApp.propTypes = {
    pageMax: PropTypes.number.isRequired,
    row: PropTypes.object.isRequired,
    group: PropTypes.object.isRequired,
    content: PropTypes.array.isRequired,
    translations: PropTypes.object.isRequired,
    storeFilterURL: PropTypes.string,
    draggableRoute: PropTypes.string,
    functions: PropTypes.object.isRequired,
    refreshRoute: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.string,
    ]),
    returnRoute: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.string,
    ]),
    addElementRoute: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.string,
    ]),
    context: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array,
    ]),
}

PaginationApp.defaultProps = {
    draggableRoute: '',
    storeFilterURL: '',
    returnRoute: '',
    addElementRoute: '',
    refreshRoute: '',
    context: {},
}