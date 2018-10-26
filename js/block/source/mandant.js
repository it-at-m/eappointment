import React, { Component, PropTypes } from 'react'
import * as Inputs from '../../lib/inputs'

class MandantView extends Component {
    constructor(props) {
        super(props)
        this.state = {
            source: props.source,
            descriptions: props.descriptions,
            labels: props.labelsmandant
        }
    }

    componentDidUpdate() {
        console.log("updated mandant component")
    }

    render() {
        const onChange = (field, value) => {
            this.props.handler(field, value)
        }

        const onChangeLabel = (_, value) => onChange('label', value)
        const onChangeSource = (_, value) => onChange('source', value)
        const onChangeName = (_, value) => onChange('contact[name]', value)
        const onChangeMail = (_, value) => onChange('contact[email]', value)
        return (
            <fieldset>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.state.labels.label}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="label"
                                value={(this.state.source) ? this.state.source.label : ''}
                                onChange={onChangeLabel}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.state.labels.source}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="source"
                                value={(this.state.source) ? this.state.source.source : ''}
                                onChange={onChangeSource}
                            />
                            <Inputs.Description
                                children={this.state.labels.description}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.state.labels.name}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="contact[name]"
                                value={(this.state.source.contact) ? this.state.source.contact.name : ''}
                                onChange={onChangeName}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.state.labels.email}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="contact[email]"
                                value={(this.state.source.contact) ? this.state.source.contact.email : ''}
                                onChange={onChangeMail}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
            </fieldset>
        )
    }
}

MandantView.propTypes = {
    labelsmandant: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired,
    handler: PropTypes.handler,
    descriptions: PropTypes.array
}

export default MandantView
