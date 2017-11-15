<template>
	<el-main>
		<el-button @click="goBack()"  icon="el-icon-back" type="primary" round>Go Back</el-button>
		<el-row type="flex" class="row-bg" justify="center">
			<el-col :span="18">
				<table>
					<tr v-for="field in fields" :key="field">
						<td class="label">{{field.name}}</td>
						<td>
							<el-input v-if="field.type=='Text' || field.type=='Number' || field.type=='Deimal Number'" v-model="field.value" :ref="field.id"></el-input>
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
							<el-button @click="addRecord()">Save</el-button>
						</td>
					</tr>
				</table>
			</el-col>
		</el-row>
	</el-main>
</template>

<script>
	module.exports = {
		data() {
			return {
				tableName: this.$route.params.tableName,
				fields: []
			}
		},
		created() {
			axios.get(`/api/table-info.php?tableName=${this.tableName}`)
				.then(result => {
					this.fields = result.data.fields;
					for (var field of this.fields) {
						field['value'] = undefined;
					}
					this.fields = JSON.parse(JSON.stringify(this.fields));
				}).catch(error => {
					console.log(error);
					this.$notify({
						message: 'Unable to fetch table information',
						type: 'error'
					});
				});
		},
		methods: {
			goBack() {
				this.$router.push(`/user/table/${this.tableName}`);
			},
			addRecord() {
				axios.post('/api/add-record.php', {
						tableName: this.tableName,
						fields: this.fields
					})
					.then(result => {
						if (result.data == 'success') {
							this.$message({
								message: 'Record successfull added',
								type: 'success'
							});
							for (var field of this.fields) {
								field['value'] = undefined;
							}
							this.fields = JSON.parse(JSON.stringify(this.fields));
						} else {
							this.$message({
								message: 'Unable to add data',
								type: 'error'
							});
						}
					}).catch(error => {
						console.log(error);
						this.$message({
							message: 'Unable to add data',
							type: 'error'
						});
					});
			}
		}

	}
</script>

<style scoped>
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
