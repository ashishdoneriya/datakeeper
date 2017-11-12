<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center">
			<el-col :span="18">
				<table>
					<tr v-for="field in fields" :key="field">
						<td>{{field.name}}</td>
						<td>
							<el-input v-if="field.type=='Text' || field.type=='Number' || field.type=='Deimal Number'"></el-input>
							<el-select v-if="field.type=='Select'" placeholder="Select" v-model="field.value">
								<el-option v-for="item in field.options" :key="item.value" :label="item.value" :value="item.value">
								</el-option>
							</el-select>
							<el-checkbox-group v-if="field.type=='Checkbox'" v-model="field.value">
								<el-checkbox v-for="option in field.options" :key="option.value" :label="option.value"></el-checkbox>
							</el-checkbox-group>
							<el-radio-group v-if="field.type=='Radio Button' && field.options.length > 0" v-model="field.value">
								<el-radio v-for="(option, j) in field.options" :key="option.value" :label="option.value">{{option.value}}</el-radio>
							</el-radio-group>
							<el-date-picker v-if="field.type=='Date'" v-model="field.value" type="date" placeholder="Pick a day">
							</el-date-picker>
							<el-time-select v-if="field.type=='Time'" v-model="field.value" :picker-options="{start: '00:15',step: '00:15',end: '23:45'}" placeholder="Select time">
							</el-time-select>
							<el-date-picker v-if="field.type=='Date Time'" v-model="field.value" type="datetime" placeholder="Select date and time"></el-date-picker>
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
			axios.get('/api/table-info.php')
				.then(result => {
					this.fields = result.data;
					for (var field of fields) {
						field['value'] = undefined;
					}
				}).catch(error => {
					this.$notify({
						message: 'Unable to fetch table information',
						type: 'error'
					});
				});
		},
		methods: {
			addRecord() {
				var data = {};
				for (var field of fields) {
					data[field['id']] = field['value'];
				}
				axios.post('/api/add-record.php', {
						tableName : this.tableName,
						fields : data
					})
					.then(result => {
						if (result == 'success') {
							this.$notify({
								message: 'Record successfull added',
								type: 'success'
							});
							this.$router.push('/user/' + this.tableName);
						}
					}).catch(error => {
						this.$notify({
							message: 'Unable to add data',
							type: 'error'
						});
					});
			}
		}

	}
</script>
