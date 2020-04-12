'use strict'

import React from "react"
import PropTypes from 'prop-types'
import { useDrag } from 'react-dnd'

export default function RenderPhotos(props) {
    const {
        images,
    } = props

    const photos = images.map(image => {
        return (<img src={image.url} key={image.basename} className={'user max150 left'} title={image.basename} />)
    })

    return (
        <div>{photos}</div>
    )
}

RenderPhotos.propTypes = {
    images: PropTypes.array.isRequired,
}
