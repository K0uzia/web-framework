"use client";

import { Target } from "lucide-react";
import React from "react";

import { BorderButton } from "@/components/shadcnblocks/border-button";
import { useGoogleFont } from "@/hooks/use-google-font";
import { cn } from "@/lib/utils";

interface RateCard2Props {
  className?: string;
}

const RateCard2 = ({ className }: RateCard2Props) => {
  const pricingPlans = [
    {
      title: "Monthly retainer",
      description:
        "Lorem ipsum dolor sit amet consectetur adipisicing elit. A quidem libero perferendis eius sunt",
      price: "$3,499",
      period: "/Month",
      features: [
        "Up to 2 active request at a time",
        "Unlimited revisions included",
        "24/7 priority support access",
        "Dedicated account manager",
      ],
    },
    {
      title: "Premium Package",
      description:
        "Lorem ipsum dolor sit amet consectetur adipisicing elit. A quidem libero perferendis eius sunt doloremque natus!",
      price: "$5,999",
      period: "/Month",
      features: [
        "Up to 5 active requests at a time",
        "Advanced analytics dashboard",
        "Custom integrations available",
        "Weekly strategy consultations",
      ],
    },
  ];

  useGoogleFont("Antonio");
  return (
    <section
      className={cn("bg-background py-32", className)}
      style={
        {
          "--font-mono": "Antonio",
        } as React.CSSProperties
      }
    >
      <div className="container flex flex-col items-center justify-center gap-8 lg:gap-15">
        <div className="flex w-full max-w-6xl flex-col items-center justify-between gap-4 lg:flex-row">
          {pricingPlans.map((plan, index) => (
            <div
              key={index}
              className="flex w-full flex-col justify-between gap-10 rounded-3xl bg-muted p-10"
            >
              <div>
                <h2 className="text-3xl font-semibold tracking-tight">
                  {plan.title}
                </h2>
                <p className="mt-4 w-full max-w-xs text-lg tracking-tight text-foreground/30">
                  {plan.description}
                </p>
              </div>
              <ul className="mt-5">
                <li className="mb-4 text-xs font-medium text-foreground/40 uppercase">
                  WHATS INCLUDED:
                </li>
                {plan.features.map((feature, featureIndex) => (
                  <li
                    key={featureIndex}
                    className="mt-1 flex items-center gap-2 font-medium"
                  >
                    <Target className="size-4" />
                    {feature}
                  </li>
                ))}
              </ul>
              <div className="mt-10 flex items-end justify-between">
                <h1 className="text-4xl font-medium tracking-tighter lg:text-7xl">
                  <span className="font-mono">{plan.price}</span>
                  <sup className="pl-2 align-top text-sm font-normal tracking-normal tracking-tight lg:text-base">
                    {plan.period}
                  </sup>
                </h1>
                <BorderButton
                  variant="outline"
                  className="relative rounded-none !px-4 uppercase shadow-none"
                >
                  Get started
                </BorderButton>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export { RateCard2 };
