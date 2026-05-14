<template>
  <div>
    <div class="page-header">
      <div><h2>课时包管理</h2><p>管理学员课时包及课消记录</p></div>
    </div>
    <el-card shadow="never">
      <div class="filter-bar">
        <el-select v-model="studentFilter" filterable placeholder="选择学员" @change="loadData" style="width:200px;margin-right:8px" clearable>
          <el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" />
        </el-select>
        <el-select v-model="statusFilter" placeholder="状态" @change="loadData" style="width:120px">
          <el-option label="全部" :value="-1"/><el-option label="有效" :value="1"/><el-option label="已用完" :value="2"/><el-option label="已过期" :value="3"/>
        </el-select>
      </div>
      <el-table :data="list" stripe v-loading="loading" border>
        <el-table-column label="学员" width="120"><template #default="{row}">{{row.student_name||'-'}}</template></el-table-column>
        <el-table-column label="类型" width="90"><template #default="{row}"><el-tag :type="['','success','primary','warning'][row.type]" size="small">{{['','1v1课时','小班课次','托管月卡'][row.type]}}</el-tag></template></el-table-column>
        <el-table-column prop="total_hours" label="总课时" width="80" align="center" />
        <el-table-column prop="used_hours" label="已用" width="80" align="center" />
        <el-table-column prop="remaining_hours" label="剩余" width="80" align="center">
          <template #default="{row}"><span :style="{color:row.remaining_hours<=5?'#f56c6c':'#67c23a',fontWeight:'bold'}">{{row.remaining_hours}}</span></template>
        </el-table-column>
        <el-table-column prop="total_amount" label="实付" width="100" align="right"><template #default="{row}">¥{{row.total_amount}}</template></el-table-column>
        <el-table-column label="过期" width="100"><template #default="{row}">{{row.expire_date>0?formatDate(row.expire_date):'-'}}</template></el-table-column>
        <el-table-column label="状态" width="70" align="center">
          <template #default="{row}"><el-tag :type="['','success','warning','danger'][row.status]||'info'" size="small">{{['','有效','已用完','已过期','已退费'][row.status]}}</el-tag></template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{row}"><el-button text type="primary" size="small" @click="showLog(row)">流水</el-button></template>
        </el-table-column>
      </el-table>
      <div class="table-footer"><el-pagination v-model:current-page="page" :page-size="pageSize" :total="total" layout="total,prev,pager,next" background small /></div>
    </el-card>
    <el-dialog v-model="showLogDialog" title="课时流水" width="700px">
      <el-table :data="logs" stripe border size="small">
        <el-table-column label="类型" width="80"><template #default="{row}"><el-tag size="small">{{['','上课耗课','购课','退费','过期失效'][row.type]}}</el-tag></template></el-table-column>
        <el-table-column prop="hours" label="课时" width="70" align="center" />
        <el-table-column prop="before_hours" label="消耗前" width="80" align="center" />
        <el-table-column prop="after_hours" label="消耗后" width="80" align="center" />
        <el-table-column prop="remark" label="说明" min-width="200" />
        <el-table-column label="时间" width="140"><template #default="{row}">{{formatDate(row.add_time)}}</template></el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import http from '../../utils/http'
const BASE='http://47.114.125.123'
const list=ref([]),page=ref(1),pageSize=15,total=ref(0),loading=ref(false)
const studentFilter=ref(''),statusFilter=ref(-1)
const students=ref([]),logs=ref([]),showLogDialog=ref(false)
async function loadData(){
  loading.value=true
  try{
    const params={page:page.value,pageSize,status:statusFilter.value}
    if(studentFilter.value)params.student_id=studentFilter.value
    const res=await http.get(BASE+'/m/Admin/c/Api/a/studentPackageList',{params})
    list.value=res.data.list||[];total.value=res.data.total||0
  }catch(e){console.error(e)}finally{loading.value=false}
}
async function loadStudents(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/students')
  students.value=res.data.list||[]
}
async function showLog(row){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/consumptionLogList',{params:{package_id:row.id,pageSize:50}})
  logs.value=res.data.list||[];showLogDialog.value=true
}
function formatDate(ts){if(!ts)return'-';const d=new Date(ts*1000);return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}
onMounted(()=>{loadData();loadStudents()})
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.page-header h2{font-size:22px;font-weight:600;color:#1a1a2e;margin-bottom:4px}
.page-header p{font-size:13px;color:#909399}
.filter-bar{margin-bottom:16px;display:flex}
.table-footer{display:flex;justify-content:flex-end;padding:16px 0 0}
</style>
