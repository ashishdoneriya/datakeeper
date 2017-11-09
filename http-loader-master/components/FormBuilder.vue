<template>
	<el-row>
		<el-row>
			<el-input placeholder="Table name" v-model="tableName"></el-input>
		</el-row>
		<el-row>
			<el-col :span="12">
				<div class="grid-content bg-purple">
					<h2>Form Builder</h2>
					<el-row>
					</el-row>
				</div>
			</el-col>
			<el-col :span="12">
				<div class="grid-content bg-purple-light">
					<h2>Preview</h2>
				</div>
			</el-col>
		</el-row>

	</el-row>
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
				let temp = this.fields[i - 1];
				this.fields[i - 1] = this.fields[i];
				this.fields[i] = temp;
			},

			shiftDown(i) {
				if (i == this.fields.length - 1) {
					return;
				}
				let temp = this.fields[i + 1];
				this.fields[i + 1] = this.fields[i];
				this.fields[i] = temp;
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
				let temp = field.options[i - 1];
				field.options[i - 1] = field.options[i];
				field.options[i] = temp;
			},

			shiftOptionDown(field, i) {
				if (i == field.options.length - 1) {
					return;
				}
				let temp = field.options[i + 1];
				field.options[i + 1] = field.options[i];
				field.options[i] = temp;
			}
		}
	};
</script>
