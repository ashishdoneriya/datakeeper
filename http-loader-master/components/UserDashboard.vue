<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
			<el-col :span="8">
				<el-card class="box-card">
					<div slot="header" class="clearfix">
						<span>Tables</span>
						<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-circle-plus" @click="addTable()">Add</el-button>
					</div>
					<div v-for="table in tablesList" :key="table" class="text item">
						{{table.displayedTableName }}

						<el-button style="float: right; padding: 3px 5px;" type="text" icon="el-icon-delete">Remove</el-button>
						<el-button style="float: right; padding: 3px 5px;margin-right:10px;" type="text" icon="el-icon-edit">Modify</el-button>
					</div>
				</el-card>
			</el-col>
		</el-row>
	</el-main>
</template>

<script>
	module.exports = {
		computed: {
			tablesList() {
				return this.$store.state.list;
			}
		},
		methods: {
			addTable() {
				this.$router.push({
					path: '/user/form-builder'
				});
			}
		}
	}
</script>


<style>
	.text {
		font-size: 14px;
	}

	.item {
		margin-bottom: 18px;
	}

	.clearfix:before,
	.clearfix:after {
		display: table;
		content: "";
	}

	.clearfix:after {
		clear: both;
	}

	.box-card {
		width: 480px;
	}
</style>