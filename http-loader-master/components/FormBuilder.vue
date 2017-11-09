<template>
	<el-main>
		<el-row>
			<el-row type="flex" class="row-bg">
				<el-col :span="8">
					<el-button icon="el-icon-back" type="primary" round><router-link to="/user/dashboard" style="color:#fff">Go Back</router-link></el-button>
				</el-col>
				<el-col :span="8">
					<el-input placeholder="Table name" v-model="tableName"></el-input>
				</el-col>
				<el-col :span="8" class="right-align">
					<el-button type="success">Save</el-button>
				</el-col>
			</el-row>
			<el-row>
				<el-col :span="12">
					<div class="grid-content bg-purple">
						<h2 class="text-center">Form Builder</h2>
						<el-row v-for="(field, i) of fields" :key="field.fieldName">
							<el-row>
								<el-col :span="24" class="right-align">
									<i style="margin-right:10px;font-size:20px;" class="el-icon-delete" @click="deleteField(i)"></i>
									<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-up" @click="shiftUp(i)"></i>
									<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-down" @click="shiftDown(i)"></i>
								</el-col>
							</el-row>
							<el-row>
								<el-col :span="24" class="right-align">
									<el-input placeholder="Field name" v-model="field.fieldName"></el-input>
								</el-col>
							</el-row>
							<div v-if="field.fieldType == 'radio' || field.fieldType == 'checkbox'  || field.fieldType == 'select'">
								<el-row v-for="(option, j) of field.options" :key="option.value">
									<el-col :span="16">
										<el-input :placeholder="getOptionPlaceholder(j)" v-model="option.value"></el-input>
									</el-col>
									<el-col :span="8">
										<i style="margin-right:10px;font-size:20px;" class="el-icon-delete" @click="removeOption(field, j)"></i>
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-up" @click="shiftOptionUp(field, j)"></i>
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-down" @click="shiftOptionDown(field, j)"></i>
									</el-col>
								</el-row>
								<el-button @click="addOption(field)" type="primary" size="small">Add Option</el-button>
							</div>
						</el-row>
					</div>
				</el-col>
				<el-col :span="12">
					<div class="grid-content bg-purple-light">
						<h2 class="text-center">Preview</h2>
					</div>
				</el-col>
			</el-row>
		</el-row>
		<div class="fab">
			<span class="fab-action-button" data-tooltip="Add Field">
							        <i class="material-icons icon-material">add</i>
							    </span>
			<ul class="fab-buttons">
				<li class="fab-buttons__item">
					<a @click="addTextInput()" class="fab-buttons__link" data-tooltip="Text">
						<i class="material-icons icon-material">text_format</i>
					</a>
				</li>
				<li class="fab-buttons__item">
					<a @click="addCheckbox()" class="fab-buttons__link" data-tooltip="Checkboxes">
						<i class="material-icons icon-material">check_box</i>
					</a>
				</li>
				<li class="fab-buttons__item">
					<a @click="addRadio()" class="fab-buttons__link" data-tooltip="Radio Buttons">
						<i class="material-icons icon-material">radio_button_checked</i>
					</a>
				</li>
				<li class="fab-buttons__item">
					<a @click="addSelect()" class="fab-buttons__link" data-tooltip="Select">
						<i class="material-icons icon-material">reorder</i>
					</a>
				</li>
				<li class="fab-buttons__item">
					<a @click="addTextArea()" class="fab-buttons__link" data-tooltip="Text Area">
						<i class="material-icons icon-material">keyboard</i>
					</a>
				</li>
			</ul>
		</div>
	</el-main>
</template>

<script>
	module.exports = {
		data() {
			return {
				fields: [],
				tableName: ""
			};
		},
		methods: {
			goBack() {},
			save() {},
			getOptionPlaceholder(j) {
				return 'Option ' + (j + 1);s
			},

			addTextInput() {
				this.fields.push({
					fieldName: "",
					fieldType: "textinput"
				});
			},

			addTextArea() {
				this.fields.push({
					fieldName: "",
					fieldType: "textarea"
				});
			},

			addCheckbox() {
				this.fields.push({
					fieldName: "",
					fieldType: "checkbox",
					options: []
				});
			},

			addRadio() {
				this.fields.push({
					fieldName: "",
					fieldType: "radio",
					options: []
				});
			},

			addSelect() {
				this.fields.push({
					fieldName: "",
					fieldType: "select",
					options: []
				});
			},

			deleteField(i) {
				this.fields.splice(i, 1);
			},

			shiftUp(i) {
				if (i == 0) {
					return;
				}
				var temp = this.fields[i - 1];
				var temp1 = this.fields[i];
				Vue.set(this.fields, i - 1, temp1);
				Vue.set(this.fields, i, temp);
			},

			shiftDown(i) {
				if (i == this.fields.length - 1) {
					return;
				}
				var temp = this.fields[i + 1];
				var temp1 = this.fields[i];
				Vue.set(this.fields, i + 1, temp1);
				Vue.set(this.fields, i, temp);
			},

			addOption(field) {
				field.options.push({
					value: ""
				});
			},

			removeOption(field, i) {
				field.options.splice(i, 1);
			},

			shiftOptionUp(field, i) {
				if (i == 0) {
					return;
				}
				var temp = field.options[i - 1];
				var temp1 = field.options[i];
				Vue.set(field.options, i - 1, temp1);
				Vue.set(field.options, i, temp);
			},

			shiftOptionDown(field, i) {
				if (i == field.options.length - 1) {
					return;
				}
				var temp = field.options[i];
				var temp1 = field.options[i + 1];
				Vue.set(field.options, i, temp1);
				Vue.set(field.options, i + 1, temp);
			}
		}
	};
</script>

<style scoped>
	.fab {
		position: fixed;
		width: 56px;
		right: 15px;
		bottom: 15px;
		margin-left: -28px;
	}

	.fab:hover .fab-buttons {
		opacity: 1;
		visibility: visible;
		cursor: pointer;
	}

	.fab:hover .fab-buttons__link {
		transform: scaleY(1) scaleX(1) translateY(-16px) translateX(0px);
	}

	.fab-action-button:hover+.fab-buttons .fab-buttons__link:before {
		visibility: visible;
		opacity: 1;
		transform: scale(1);
		transform-origin: right center 0;
		transition-delay: 0.3s;
	}

	.fab-action-button {
		position: absolute;
		bottom: 0;
		display: block;
		width: 56px;
		height: 56px;
		background-color: #29B6F6;
		border-radius: 50%;
		box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
	}

	.fab-buttons {
		position: absolute;
		left: 0;
		right: 0;
		bottom: 50px;
		list-style: none;
		margin: 0;
		padding: 0;
		opacity: 0;
		visibility: hidden;
		transition: 0.2s;
	}

	.fab-buttons__item {
		display: block;
		text-align: center;
		margin: 12px 0;
	}

	.fab-buttons__link {
		display: inline-block;
		width: 40px;
		height: 40px;
		text-decoration: none;
		background-color: #ffffff;
		border-radius: 50%;
		box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
		transform: scaleY(0.5) scaleX(0.5) translateY(0px) translateX(0px);
		-moz-transition: .3s;
		-webkit-transition: .3s;
		-o-transition: .3s;
		transition: .3s;
	}

	[data-tooltip]:before {
		top: 50%;
		margin-top: -11px;
		font-weight: 600;
		border-radius: 2px;
		background: #585858;
		color: #fff;
		content: attr(data-tooltip);
		font-size: 12px;
		text-decoration: none;
		visibility: hidden;
		opacity: 0;
		padding: 4px 7px;
		margin-right: 12px;
		position: absolute;
		transform: scale(0);
		right: 100%;
		white-space: nowrap;
		transform-origin: top right;
		transition: all .3s cubic-bezier(.25, .8, .25, 1);
	}

	[data-tooltip]:hover:before {
		visibility: visible;
		opacity: 1;
		transform: scale(1);
		transform-origin: right center 0;
	}

	.icon-material {
		display: inline-block;
		width: 40px;
		height: 40px;
	}
</style>
