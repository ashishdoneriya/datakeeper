<template>
	<el-main>
		<el-row>
			<el-row type="flex" class="row-bg" style="margin-bottom:15px">
				<el-col :span="8">
					<el-button icon="el-icon-back" type="primary" round>
						<router-link to="/user/dashboard" style="color:#fff">Go Back</router-link>
					</el-button>
				</el-col>
				<el-col :span="8">
					<el-input placeholder="Table name" v-model="displayedTableName"></el-input>
				</el-col>
				<el-col :span="8" class="right-align">
					<el-button type="success" @click="save()">Save</el-button>
				</el-col>
			</el-row>
			<el-row style="height:calc(100vh - 180px)">
				<el-col :span="12" style="border-right:1px solid #ccc;overflow-y:auto;overflow-x:hidden;height:100%;padding-right:30px;">
					<div class="grid-content bg-purple">
						<h2 class="text-center">Form Builder</h2>
						<el-card class="box-card" v-for="(field, i) of fields" :key="field.fieldName" style="margin-bottom:10px;width:100%">
							<el-row v-show="i != 0">
								<el-col :span="24" class="right-align clearfix" style="margin-bottom:5px;">
									<el-tooltip content="Remove Field" placement="top" open-delay="200">
										<i style="margin-right:10px;font-size:20px;" class="el-icon-delete" @click="deleteField(i)"></i>
									</el-tooltip>
									<el-tooltip v-show="i != 1" content="Shift Field Up" placement="top" open-delay="200">
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-up" @click="shiftUp(i)"></i>
									</el-tooltip>
									<el-tooltip v-show="i != (fields.length - 1)" content="Shift Field Down" placement="top" open-delay="200">
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-down" @click="shiftDown(i)"></i>
									</el-tooltip>
								</el-col>
							</el-row>
							<el-form :label-position="'left'" ref="form" label-width="120px">
								<el-form-item label="Field Name">
									<el-input v-model="field.name"></el-input>
								</el-form-item>
								<el-form-item label="Field Type">
									<span v-if="field.type == 'Id'" >Id</span>
									<el-select v-if="field.type != 'Id'" v-model="field.type" placeholder="Select">
										<el-option v-for="type in fieldTypes" :key="type.value" :label="type.label" :value="type.value">
										</el-option>
									</el-select>
								</el-form-item>
								<el-form-item label="Options" v-if="isFieldEligible(field.type)">
									<el-col :span="24" v-for="(option, j) of field.options" :key="option" style="margin-bottom:3px;">
										<el-input v-model="option.value" :placeholder="'Option ' + (j+1)" style="width:calc(100% - 120px);margin-right:10px;"></el-input>
										<i style="margin-right:10px;font-size:20px;" class="el-icon-delete" @click="removeOption(field, j)"></i>
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-up" @click="shiftOptionUp(field, j)"></i>
										<i style="margin-right:10px;font-size:20px;" class="el-icon-arrow-down" @click="shiftOptionDown(field, j)"></i>
									</el-col>
									<el-button @click="addOption(field)" type="primary" size="small">Add Option</el-button>
								</el-form-item>
								<el-form-item label="Is Compulsory" v-if="field.type != 'Id'">
									<el-checkbox v-model="field.isCompulsory"></el-checkbox>
								</el-form-item>
								<el-form-item label="Auto Increment" v-if="field.type == 'Id'">
									<el-checkbox v-model="field.autoIncrement"></el-checkbox>
								</el-form-item>
							</el-form>
						</el-card>
					</div>
				</el-col>
				<el-col :span="12" style="overflow-y:auto;overflow-x:hidden;height:100%;padding-right:30px;">
					<div class="grid-content bg-purple-light" style="padding-left:30px;">
						<h2 class="text-center">Preview</h2>
						<table v-show="fields.length > 0">
							<tr v-for="field in fields" :key="field" v-show="field.type != 'Id' || (field.type == 'Id' && field.autoIncrement==false)">
								<td class="label">{{field.name}}</td>
								<td>
									<el-input v-if="field.type=='Text' || field.type=='Number' || field.type=='Deimal Number' || field.type=='Id'" v-model="field.value" :ref="field.id"></el-input>
									<el-select v-if="field.type=='Select'" placeholder="Select" v-model="field.value" :ref="field.id">
										<el-option v-for="item in field.options" :key="item.value" :label="item.value" :value="item.value">
										</el-option>
									</el-select>
									<el-checkbox-group v-if="field.type=='Checkbox'" v-model="field.value" :ref="field.id">
										<el-checkbox v-for="option in field.options" :key="option.value" :label="option.value"></el-checkbox>
									</el-checkbox-group>
									<el-radio-group :ref="field.id" v-if="field.type=='Radio Button' && field.options.length > 0" v-model="field.value">
										<el-radio v-for="(option, j) in field.options" :key="option.value" :label="option.value">{{option.value}}</el-radio>
									</el-radio-group>
									<el-date-picker :ref="field.id" v-if="field.type=='Date'" v-model="field.value" type="date" placeholder="Pick a day" value-format="yyyy-MM-dd">
									</el-date-picker>
									<el-time-select :ref="field.id" v-if="field.type=='Time'" v-model="field.value" value-format="HH:mm:ss" :picker-options="{start: '00:15',step: '00:15',end: '23:45'}" placeholder="Select time">
									</el-time-select>
									<el-date-picker :ref="field.id" v-if="field.type=='Date Time'" v-model="field.value" type="datetime" placeholder="Select date and time" value-format="yyyy-MM-dd HH:mm:ss"></el-date-picker>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<el-button>Save</el-button>
								</td>
							</tr>
						</table>
					</div>
				</el-col>
			</el-row>
		</el-row>
		<a class="float" @click="addField()">
			<i class="material-icons my-float" style="font-size: 40px;margin-top: 11px;margin-left: 2px;">add</i>
		</a>
	</el-main>
</template>

<script>
	module.exports = {
		data() {
			return {
				tableName: '',
				displayedTableName: '',
				fields: [],
				fieldTypes: [{
						value: 'Select',
						label: 'Select'
					},
					{
						value: 'Checkbox',
						label: 'Checkbox'
					},
					{
						value: 'Radio Button',
						label: 'Radio Button'
					},
					{
						value: 'Text',
						label: 'Text'
					},
					{
						value: 'Number',
						label: 'Number'
					},
					{
						value: 'Deimal Number',
						label: 'Deimal Number'
					},
					{
						value: 'Date',
						label: 'Date'
					},
					{
						value: 'Time',
						label: 'Time'
					},
					{
						value: 'Date Time',
						label: 'Date Time'
					}
				]
			};
		},
		created: function() {
			this.tableName = this.$route.params.tableName;
			if (this.tableName) {
				axios.get(`/api/table-info.php?tableName=${this.tableName}`)
					.then(result => {
						this.displayedTableName = result.data.displayedTableName;
						this.fields = result.data.fields;
						for (var field of this.fields) {
							field['value'] = undefined;
						}
					}).catch(error => {
						this.$message({
							message: 'Unable to fetch table information',
							type: 'error'
						});
					});
			} else {
				this.fields.push({
					name: 'Id',
					type: 'Id',
					isCompulsory: true,
					value: '',
					isVisible : false,
					autoIncrement : true
				});
			}
		},
		methods: {
			goBack() {},
			save() {
				var temp = [];
				for (var field of this.fields) {
					if (field.name.trim().length == 0) {
						continue;
					}
					if (this.isFieldEligible(field.type) && field.options.length == 0) {
						continue;
					}
					temp.push(field);
				}
				if (this.fields.length == 0) {
					var word = this.tableName ? "saving" : "adding";
					this.$message({
						message: `Not ${word} table since some fields are not properly filled`,
						type: 'error'
					});
					return;
				}
				if (this.displayedTableName.trim().length == 0) {
					this.$message({
						message: 'Please specify table name',
						type: 'error'
					});
					return;
				}
				if (this.tableName == undefined || this.tableName == '') {
					this.addTable();
				} else {
					this.updateTable();
				}
			},

			addTable() {
				axios.post('/api/add-table.php', {
					'displayedTableName': this.displayedTableName,
					'fields': this.fields
				}).then(response => {
					this.$message({
						message: 'Table added successfully',
						type: 'success'
					});
					this.$store.commit('update');
					setTimeout(() => {
						this.$router.push('/user/dashboard');
					}, 1000);
				}).catch(error => {
					this.showError('Unable to add table');
				});
			},
			showError(msg) {
				this.$message({
					message: msg,
					type: 'error'
				});
			},
			updateTable() {
				axios.post('/api/update-table.php', {
					'tableName': this.tableName,
					'displayedTableName': this.displayedTableName,
					'fields': this.fields
				}).then(response => {
					if (response.data == 'success') {
						this.$message({
							message: 'Table updated',
							type: 'success'
						});
						this.$store.commit('update');
						setTimeout(() => {
							this.$router.push('/user/dashboard');
						}, 2000);
					} else {
						this.showError('Unable to update table');
					}
				}).catch(error => {

				});
			},
			isFieldEligible(type) {
				if (!type) {
					return false;
				}
				switch (type) {
					case 'Select':
					case 'Checkbox':
					case 'Radio Button':
						return true;
					default:
						return false;
				}
			},

			addField() {
				this.fields.push({
					name: '',
					type: '',
					isCompulsory: false,
					options: [],
					value: undefined,
					isVisible : false
				});
			},

			deleteField(i) {
				if (this.fields[i].type == 'id') {
					return;
				}
				this.fields.splice(i, 1);
			},

			shiftUp(i) {
				if (i == 0 || i == 1) {
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
	.float {
		position: fixed;
		width: 60px;
		height: 60px;
		bottom: 40px;
		right: 40px;
		background-color: #F44336;
		color: #FFF;
		border-radius: 50px;
		text-align: center;
		box-shadow: 2px 2px 3px #999;
	}

	.my-float {
		margin-top: 22px;
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
		table {
		width: 100%;
	}

	tr>td {
		padding-bottom: 10px;
	}

	.label {
		font-size: 14px;
		color: #5a5e66;
	}
</style>

