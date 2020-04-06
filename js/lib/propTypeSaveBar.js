import PropTypes from 'prop-types'

const propsTypeSaveBar = PropTypes.shape({
    SaveBar: PropTypes.arrayOf(
        PropTypes.shape({
            lastSave: PropTypes.oneOfType([
                PropTypes.date,
                PropTypes.number
            ]).required
        })
    )
})


export default propsTypeSaveBar