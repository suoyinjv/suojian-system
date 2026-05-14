<template>
  <div>
    <div class="page-header">
      <div><h2>1v1 预约管理</h2><p>管理一对一课程预约</p></div>
      <el-button type="primary" @click="showAdd=true">+ 新建预约</el-button>
    </div>
    <el-card shadow="never">
      <div class="filter-bar"><el-date-picker v-model="dateFilter" type="date" placeholder="筛选日期" value-format="YYYY-MM-DD" @change="loadData" style="width:160px;margin-right:8px" clearable />
        <el-select v-model="statusFilter" placeholder="状态" @change="loadData" style="width:120px"><el-option label="全部" :value="-1"/><el-option label="待确认" :value="0"/><el-option label="已确认" :value="1"/><el-option label="已完成" :value="2"/><el-option label="已取消" :value="3"/></el-select>
      </div>
      <el-table :data="list" stripe v-loading="loading" border>
        <el-table-column label="学员" width="120"><template #default="{row}">{{row.student_name||'-'}}</template></el-table-column>
        <el-table-column label="老师" width="120"><template #default="{row}">{{row.teacher_name||'-'}}</template></el-table-column>
        <el-table-column prop="schedule_date" label="日期" width="120" />
        <el-table-column label="时间" width="130"><template #default="{row}">{{row.start_time}}-{{row.end_time}}</template></el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{row}"><el-tag :type="['info','primary','success','danger'][row.status]||'info'" size="small">{{['待确认','已确认','已完成','已取消'][row.status]||'未知'}}</el-tag></template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="150" />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{row}">
            <el-button v-if="row.status==0" text type="primary" size="small" @click="confirmRow(row)">确认</el-button>
            <el-button v-if="row.status==1" text type="success" size="small" @click="completeRow(row)">完成</el-button>
            <el-button v-if="row.status<2" text type="danger" size="small" @click="cancelRow(row)">取消</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="table-footer"><el-pagination v-model:current-page="page" :page-size="pageSize" :total="total" layout="total,prev,pager,next" background small /></div>
    </el-card>
    <el-dialog v-model="showAdd" title="新建预约" width="500px">
      <el-form :model="form" label-width="80px">
        <el-form-item label="学员" required><el-select v-model="form.student_id" filterable style="width:100%"><el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" /></el-select></el-form-item>
        <el-form-item label="老师" required><el-select v-model="form.teacher_id" filterable style="width:100%"><el-option v-for="t in teachers" :key="t.id" :label="t.username" :value="t.id" /></el-select></el-form-item>
        <el-form-item label="日期" required><el-date-picker v-model="form.schedule_date" type="date" value-format="YYYY-MM-DD" style="width:100%" /></el-form-item>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="开始" required><el-time-picker v-model="form.start_time" format="HH:mm" value-format="HH:mm:ss" style="width:100%" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="结束" required><el-time-picker v-model="form.end_time" format="HH:mm" value-format="HH:mm:ss" style="width:100%" /></el-form-item></el-col>
        </el-row>
        <el-form-item label="备注"><el-input v-model="form.remark" type="textarea" :rows="2" /></el-form-item>
      </el-form>
      <template #footer><el-button @click="showAdd=false">取消</el-button><el-button type="primary" @click="handleSave" :loading="saving">保存</el-button></template>
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import http from '../../utils/http'
const BASE='http://47.114.125.123'
const list=ref([]),page=ref(1),pageSize=15,total=ref(0),loading=ref(false)
const showAdd=ref(false),saving=ref(false)
const dateFilter=ref(''),statusFilter=ref(-1)
const students=ref([]),teachers=ref([])
const form=reactive({student_id:0,teacher_id:0,schedule_date:'',start_time:'',end_time:'',remark:''})
async function loadData(){
  loading.value=true
  try{
    const params={page:page.value,pageSize,status:statusFilter.value}
    if(dateFilter.value)params.schedule_date=dateFilter.value
    const res=await http.get(BASE+'/m/Admin/c/Api/a/reservationList',{params})
    list.value=res.data.list||[];total.value=res.data.total||0
  }catch(e){console.error(e)}finally{loading.value=false}
}
async function loadStudents(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/students')
  students.value=res.data.list||[]
}
async function loadTeachers(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/teachers')
  teachers.value=res.data.list||[]
}
async function confirmRow(row){await http.get(BASE+'/m/Admin/c/Api/a/reservationConfirm',{params:{id:row.id}});ElMessage.success('已确认');loadData()}
async function completeRow(row){await http.get(BASE+'/m/Admin/c/Api/a/reservationComplete',{params:{id:row.id}});ElMessage.success('已完成');loadData()}
async function cancelRow(row){await http.get(BASE+'/m/Admin/c/Api/a/reservationCancel',{params:{id:row.id}});ElMessage.success('已取消');loadData()}
async function handleSave(){
  if(!form.student_id||!form.teacher_id||!form.schedule_date||!form.start_time){ElMessage.warning('请填写完整');return}
  saving.value=true
  try{
    const p=new URLSearchParams();Object.entries(form).forEach(([k,v])=>{if(v)p.append(k,v)})
    await http.post(BASE+'/m/Admin/c/Api/a/reservationCreate',p.toString(),{headers:{'Content-Type':'application/x-www-form-urlencoded'}})
    ElMessage.success('预约成功');showAdd.value=false;loadData()
  }catch(e){console.error(e);ElMessage.error('操作失败')}finally{saving.value=false}
}
onMounted(()=>{loadData();loadStudents();loadTeachers()})
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.page-header h2{font-size:22px;font-weight:600;color:#1a1a2e;margin-bottom:4px}
.page-header p{font-size:13px;color:#909399}
.filter-bar{margin-bottom:16px;display:flex}
.table-footer{display:flex;justify-content:flex-end;padding:16px 0 0}
</style>
