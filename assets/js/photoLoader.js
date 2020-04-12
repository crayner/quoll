'use strict';

import React from 'react'
import { render } from 'react-dom'
import PhotoLoaderApp from "./PhotoLoader/PhotoLoaderApp";

const photoLoaderWrapper = document.getElementById('photo_loader')

if (photoLoaderWrapper === null)
    render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
else
    render(
        <PhotoLoaderApp
            {...window.PHOTOLOADER_PROPS}
        />,
        photoLoaderWrapper
    )
