import React, { Component, PropTypes } from 'react'
import MandantView from './mandant'
import RequestsView from './requests'
import ProvidersView from './providers'

class SourceView extends Component {
    constructor(props) {
        super(props)
        this.handler = this.handler.bind(this)
        this.state = {
            labels: props.labels,
            descriptions: props.descriptions,
            source: props.source
        }
    }

    handler(field, value) {
        let newstate = this.state.source;
        if (field.match(/.\[/)) {
            const firstPart = field.split('[')[0];
            const secondPart = field.split('[')[1].replace(']', '');
            newstate[firstPart] = { [secondPart]: value }
        }
        else {
            newstate[field] = value;
        }
        this.setState({ source: newstate });
    }

    componentDidMount() {
        console.log("mounted component")
    }

    componentDidUpdate() {
        console.log("updated component")
    }

    render() {
        return (
            <div>
                <MandantView {...this.props} handler={this.handler} />
                <fieldset>
                    <legend>Dienstleistungen</legend>
                    <RequestsView {...this.props} source={this.state.source} />
                </fieldset>
                <fieldset>
                    <legend>Dienstleister</legend>
                    <ProvidersView {...this.props} source={this.state.source} />
                </fieldset>
            </div>
        );
    }
}

SourceView.propTypes = {
    requests: PropTypes.array,
    labels: PropTypes.array.isRequired,
    descriptions: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired
}

export default SourceView
