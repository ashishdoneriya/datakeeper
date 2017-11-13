<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center">
			<el-col :span="12">
				<el-input placeholder="Search.." v-model="query">
					<el-button @click="search" slot="append" icon="el-icon-search">Search</el-button>
				</el-input>
			</el-col>
		</el-row>
		<el-row>
			<el-col :span="24">
				<el-table :data="tableData">
					<el-table-column v-for="field in fields" :key="field" :prop="field.id" :label="field.name">
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>
		<a class="float" @click="addRecord()">
			<i class="material-icons my-float" style="font-size: 40px;margin-top: 11px;margin-left: 2px;">add</i>
		</a>
		<router-link :to="'/user/table/' + tableName + '/add-record'" class="float">
			<i class="material-icons my-float" style="font-size: 40px;margin-top: 11px;margin-left: 2px;">add</i>
		</router-link>
	</el-main>
</template>

<script>
	module.exports = {
		data() {
			return {
				tableName: this.$route.params.tableName,
				fields: [],
				tableData: [],
				searchQuery: '',
				displayedTableName : '',
			};
		},
		created() {
			this.search();
		},
		methods: {
			search() {
				var data = {
					tableName: this.tableName
				}
				axios.get(`/api/search.php?tableName=${this.tableName}`)
					.then(result => {
						this.tableData = result.data;
					}).catch(error => {
						this.$message({
							message: 'Unable to fetch records',
							type: 'error'
						});
					});
			},
			getTableInfo() {
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
			}
		}
	}
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
</style>
