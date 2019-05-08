import React, { Component, PropTypes } from 'react'
import * as Inputs from '../../lib/inputs'

class MandantView extends Component {
    constructor(props) {
        super(props)
    }

    componentDidUpdate() {
        //console.log("updated mandant component")
    }

    render() {
        const onChange = (field, value) => {
            if (field == 'source') {
                value = value.substring(0,10);
            }
            this.props.changeHandler(field, value)
        }

        return (
            <fieldset>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.label}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="label"
                                value={(this.props.source) ? this.props.source.label : ''}
                                onChange={onChange}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.source}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="source"
                                value={(this.props.source) ? this.props.source.source : ''}
                                attributes={ {readOnly: this.props.source.lastChange, maxLength: 10} }
                                onChange={onChange}
                            />
                            <Inputs.Description
                                children={this.props.descriptions.mandantlabel}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.name}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="contact[name]"
                                value={(this.props.source.contact) ? this.props.source.contact.name : ''}
                                onChange={onChange}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <div>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.email}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="contact[email]"
                                value={(this.props.source.contact) ? this.props.source.contact.email : ''}
                                onChange={onChange}
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
    changeHandler: PropTypes.handler,
    descriptions: PropTypes.array
}

export default MandantView
