import React from 'react'
import PropTypes from 'prop-types'

const titleAside = (title) => {
    if (title) {
        return (<div className="left aside">{title}</div>)
    }
}

const headerRight = (header) => {
    if (header) {
        return (<div className="right header_right">{header}</div>)
    }
}

const headerMiddle = (title) => {
    if (title) {
        return (<div className="middle header_middle">{title}</div>)
    }
}

const Board = (props) => {
    const className = `board ${props.className}`

    return (
        <section className={className}>
            {props.titleAside || props.headerRight || props.headerMiddle ?
            <div className="board__actions">
                {titleAside(props.titleAside)}
                {headerMiddle(props.title)}
                {headerRight(props.headerRight)}
            </div> : null }
            <div className="board__body body">
                {props.body}
            </div>
            {props.footer ?
             <div className="board__footer footer">
                {props.footer}
            </div> : null }
        </section>
    )
}

Board.propTypes = {
    className: PropTypes.string,
    title: PropTypes.node.isRequired,
    titleAside: PropTypes.node,
    headerRight: PropTypes.node,
    headerMiddle: PropTypes.node,
    body: PropTypes.node.isRequired,
    footer: PropTypes.node
}

export default Board
