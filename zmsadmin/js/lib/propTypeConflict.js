import PropTypes from 'prop-types'
import propTypeAvailability from "./propTypeAvailability"

const propsTypeConflict = PropTypes.shape({
    type: PropTypes.oneOf(['conflict']),
    amendment: PropTypes.string,
    appointments: PropTypes.arrayOf(
        PropTypes.shape({
            availability: propTypeAvailability,
            date: PropTypes.oneOfType([
                PropTypes.string,
                PropTypes.number
            ]) 
        })
    ),
    endTime: PropTypes.string
})


export default propsTypeConflict