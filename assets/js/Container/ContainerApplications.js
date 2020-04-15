'use strict'

let Applications = {};

Applications['DepartmentEdit'] = require('../Department/DepartmentEditApp').default;
Applications['LibraryApp'] = require('../Library/LibraryApp').default;
Applications['BrowseApp'] = require('../Library/BrowseApp').default;
Applications['QuickLoanApp'] = require('../Library/QuickLoanApp').default;

export default Applications