import PropTypes from 'prop-types'

const propsTypeAvailability = PropTypes.shape({
    type: PropTypes.oneOf(['appointment', 'openinghours']),
    description: PropTypes.string,
    startTime: PropTypes.string,
    endTime: PropTypes.string
})


export default propsTypeAvailability