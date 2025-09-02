/**
 * Enhanced Design System Composable
 *
 * This composable provides utilities and helpers for the enhanced design language
 * including status colors, gradients, and common styling patterns.
 */

export const useDesignSystem = () => {
    // Status dot color classes
    const getStatusDotColor = (status: string): string => {
        const statusMap: Record<string, string> = {
            completed: 'status-dot-success',
            succeeded: 'status-dot-success',
            running: 'status-dot-running',
            failed: 'status-dot-error',
            error: 'status-dot-error',
            pending: 'status-dot-warning',
            waiting: 'status-dot-warning',
            active: 'status-dot-success',
            inactive: 'status-dot text-gray-400',
            default: 'status-dot text-gray-500',
        };

        return statusMap[status.toLowerCase()] || statusMap.default;
    };

    // Status variant for badges
    const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
        const statusMap: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            completed: 'default',
            succeeded: 'default',
            running: 'outline',
            failed: 'destructive',
            error: 'destructive',
            pending: 'secondary',
            waiting: 'secondary',
            active: 'default',
            inactive: 'secondary',
        };

        return statusMap[status.toLowerCase()] || 'secondary';
    };

    // Enhanced card gradient classes
    const getCardGradient = (type: 'blue' | 'green' | 'purple' | 'amber' | 'default' = 'default'): string => {
        const gradientMap: Record<string, string> = {
            blue: 'bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20',
            green: 'bg-gradient-to-br from-green-50/30 via-emerald-50/20 to-teal-50/30 dark:from-green-900/20 dark:via-emerald-900/15 dark:to-teal-900/20',
            purple: 'bg-gradient-to-br from-purple-50/30 via-indigo-50/20 to-blue-50/30 dark:from-purple-900/20 dark:via-indigo-900/15 dark:to-blue-900/20',
            amber: 'bg-gradient-to-br from-amber-50/30 via-orange-50/20 to-red-50/30 dark:from-amber-900/20 dark:via-orange-900/15 dark:to-red-900/20',
            default:
                'bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20',
        };

        return gradientMap[type];
    };

    // Enhanced button classes
    const getButtonClasses = (variant: 'gradient' | 'outline' | 'ghost' = 'gradient'): string => {
        const buttonMap: Record<string, string> = {
            gradient: 'btn-gradient',
            outline: 'btn-outline-enhanced',
            ghost: 'hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors',
        };

        return buttonMap[variant] || buttonMap.gradient;
    };

    // JSON display classes
    const getJsonDisplayClasses = (type: 'green' | 'blue' | 'amber' = 'blue'): { container: string; text: string } => {
        const classMap: Record<string, { container: string; text: string }> = {
            green: {
                container: 'json-display-green',
                text: 'json-text-green',
            },
            blue: {
                container: 'json-display-blue',
                text: 'json-text-blue',
            },
            amber: {
                container: 'json-display-amber',
                text: 'json-text-amber',
            },
        };

        return classMap[type] || classMap.blue;
    };

    // Page layout helper classes
    const getPageLayoutClasses = () => {
        return {
            container: 'page-container',
            wrapper: 'content-wrapper',
            header: 'page-header',
            headerGradient: 'page-header-gradient',
            headerContent: 'page-header-content',
            headerLayout: 'page-header-layout',
            headerInfo: 'page-header-info',
            headerTitleGroup: 'page-header-title-group',
            headerIcon: 'page-header-icon',
            title: 'page-title',
            subtitle: 'page-subtitle',
            actions: 'page-actions',
        };
    };

    // Enhanced card helper classes
    const getCardClasses = () => {
        return {
            base: 'enhanced-card',
            gradient: 'enhanced-card-gradient',
            header: 'enhanced-card-header',
            title: 'enhanced-card-title',
            icon: 'enhanced-card-icon',
            content: 'enhanced-card-content',
        };
    };

    // Table helper classes
    const getTableClasses = () => {
        return {
            container: 'enhanced-table',
            header: 'enhanced-table-header',
            head: 'enhanced-table-head',
            row: 'enhanced-table-row',
        };
    };

    // Metric card helper classes
    const getMetricCardClasses = () => {
        return {
            base: 'metric-card',
            label: 'metric-card-label',
            value: 'metric-card-value',
        };
    };

    // Code display helper classes
    const getCodeClasses = () => {
        return {
            display: 'code-display',
            block: 'code-block',
        };
    };

    // Workflow indicator classes
    const getWorkflowIndicatorClasses = () => {
        return 'workflow-indicator';
    };

    // Enhanced pagination classes
    const getPaginationClasses = () => {
        return {
            container: 'enhanced-pagination',
            buttons: 'enhanced-pagination-buttons',
        };
    };

    // Format numbers with proper locale
    const formatNumber = (value: number | null | undefined): string => {
        if (value === null || value === undefined) return '-';
        return value.toLocaleString();
    };

    // Format date with proper locale
    const formatDate = (date: string | null | undefined): string => {
        if (!date) return '-';
        return new Date(date).toLocaleString();
    };

    // Format time only
    const formatTime = (timestamp: string | null | undefined): string => {
        if (!timestamp) return '-';
        return new Date(timestamp).toLocaleTimeString();
    };

    // Copy to clipboard utility
    const copyToClipboard = async (text: string): Promise<boolean> => {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            return false;
        }
    };

    return {
        // Status utilities
        getStatusDotColor,
        getStatusVariant,

        // Layout utilities
        getPageLayoutClasses,
        getCardClasses,
        getTableClasses,
        getMetricCardClasses,
        getCodeClasses,

        // Styling utilities
        getCardGradient,
        getButtonClasses,
        getJsonDisplayClasses,
        getWorkflowIndicatorClasses,
        getPaginationClasses,

        // Data formatting utilities
        formatNumber,
        formatDate,
        formatTime,
        copyToClipboard,
    };
};

// Type definitions for better TypeScript support
export type StatusType = 'completed' | 'succeeded' | 'running' | 'failed' | 'error' | 'pending' | 'waiting' | 'active' | 'inactive';
export type CardGradientType = 'blue' | 'green' | 'purple' | 'amber' | 'default';
export type ButtonVariantType = 'gradient' | 'outline' | 'ghost';
export type JsonDisplayType = 'green' | 'blue' | 'amber';
