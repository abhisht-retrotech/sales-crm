import ProcessTriggerNode from './ProcessTriggerNode';
import ProcessApprovalStepNode from './ProcessApprovalStepNode';
import ProcessDecisionNode from './ProcessDecisionNode';
import ProcessScreenNode from './ProcessScreenNode';
import ProcessActionNode from './ProcessActionNode';

export const nodeTypes = {
  processTrigger: ProcessTriggerNode,
  processApprovalStep: ProcessApprovalStepNode,
  processDecision: ProcessDecisionNode,
  processScreen: ProcessScreenNode,
  processAction: ProcessActionNode
};

export {
  ProcessTriggerNode,
  ProcessApprovalStepNode,
  ProcessDecisionNode,
  ProcessScreenNode,
  ProcessActionNode
};
