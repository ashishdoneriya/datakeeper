<template>
	<el-container>
		<el-header>
			<el-menu :default-active="'1'" class="el-menu-demo" mode="horizontal" background-color="#545c64" text-color="#fff" active-text-color="#ffd04b">
				<el-menu-item index="1">Home</el-menu-item>
				<el-menu-item index="2">
					<el-select v-model="value" placeholder="Select Table">
						<el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
						</el-option>
					</el-select>
				</el-menu-item>
			</el-menu>
		</el-header>
	</el-container>
</template>

<script>
	module.exports = {
		data() {
			return {
				options: [{
						value: "Option1",
						label: "Option1"
					},
					{
						value: "Option2",
						label: "Option2"
					},
					{
						value: "Option3",
						label: "Option3"
					},
					{
						value: "Option4",
						label: "Option4"
					},
					{
						value: "Option5",
						label: "Option5"
					}
				],
				value: ""
			};
		}
	};
</script>