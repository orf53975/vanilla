/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { IOptionalComponentID } from "@library/componentIDs";
import { ButtonBaseClass } from "@library/components/forms/Button";
import SmartLink from "@library/routing/links/SmartLink";
import classNames from "classnames";
import React from "react";
import { LinkProps } from "react-router-dom";

interface IProps extends IOptionalComponentID, LinkProps {
    children: React.ReactNode;
    className?: string;
    to: string;
    title?: string;
    ariaLabel?: string;
    baseClass?: ButtonBaseClass;
}

/**
 * A Link component that looks like a Button component.
 */
export default class LinkAsButton extends React.Component<IProps> {
    public static defaultProps: Partial<IProps> = {
        baseClass: ButtonBaseClass.STANDARD,
    };

    public render() {
        const { baseClass, className, title, ariaLabel, to, children, ...restProps } = this.props;
        const componentClasses = classNames(baseClass, className);
        return (
            <SmartLink
                className={componentClasses}
                title={title}
                aria-label={ariaLabel || title}
                tabIndex={-1}
                to={to}
                {...restProps}
            >
                {children}
            </SmartLink>
        );
    }
}
